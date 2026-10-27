<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function create(array $data): Attendance
    {
        return DB::transaction(function () use ($data): Attendance {
            $payload = $this->prepareData($data);

            $this->ensureUniqueAttendance($payload);
            $this->ensureCheckOutAfterCheckIn($payload);
            $this->calculateWorkMinutes($payload);
            $this->fillAuditColumns($payload, true);

            return Attendance::create($payload);
        });
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($attendance, $data): Attendance {
            $payload = $this->prepareData($data);

            $this->ensureUniqueAttendance($payload, $attendance);
            $this->ensureCheckOutAfterCheckIn($payload, $attendance);
            $this->calculateWorkMinutes($payload, $attendance);
            $this->fillAuditColumns($payload);

            $attendance->update($payload);

            return $attendance->refresh();
        });
    }

    public function delete(Attendance $attendance): bool
    {
        return DB::transaction(fn (): bool => (bool) $attendance->delete());
    }

    private function prepareData(array $data): array
    {
        $payload = Arr::only($data, [
            'employee_id',
            'attendance_date',
            'check_in_at',
            'check_out_at',
            'status',
            'work_minutes',
            'remarks',
        ]);

        foreach (array_keys($payload) as $column) {
            if (! Schema::hasColumn('attendances', $column)) {
                unset($payload[$column]);
            }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function ensureUniqueAttendance(array $payload, ?Attendance $attendance = null): void
    {
        if (
            ! Schema::hasColumn('attendances', 'employee_id')
            || ! Schema::hasColumn('attendances', 'attendance_date')
        ) {
            return;
        }

        $employeeId = $payload['employee_id'] ?? $attendance?->employee_id;
        $attendanceDate = $payload['attendance_date'] ?? $attendance?->attendance_date;

        if (blank($employeeId) || blank($attendanceDate)) {
            return;
        }

        $exists = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $attendanceDate)
            ->when($attendance !== null, fn ($query) => $query->whereKeyNot($attendance->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'attendance_date' => 'Attendance already exists for this employee on this date.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function ensureCheckOutAfterCheckIn(array $payload, ?Attendance $attendance = null): void
    {
        if (
            ! Schema::hasColumn('attendances', 'check_in_at')
            || ! Schema::hasColumn('attendances', 'check_out_at')
        ) {
            return;
        }

        $checkIn = $payload['check_in_at'] ?? $attendance?->check_in_at;
        $checkOut = $payload['check_out_at'] ?? $attendance?->check_out_at;

        if (blank($checkIn) || blank($checkOut)) {
            return;
        }

        if (Carbon::parse($checkOut)->lessThanOrEqualTo(Carbon::parse($checkIn))) {
            throw ValidationException::withMessages([
                'check_out_at' => 'The check-out time must be after the check-in time.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function calculateWorkMinutes(array &$payload, ?Attendance $attendance = null): void
    {
        if (
            ! Schema::hasColumn('attendances', 'work_minutes')
            || ! Schema::hasColumn('attendances', 'check_in_at')
            || ! Schema::hasColumn('attendances', 'check_out_at')
        ) {
            return;
        }

        $checkIn = $payload['check_in_at'] ?? $attendance?->check_in_at;
        $checkOut = $payload['check_out_at'] ?? $attendance?->check_out_at;

        if (blank($checkIn) || blank($checkOut)) {
            if (
                $attendance === null
                || array_key_exists('check_in_at', $payload)
                || array_key_exists('check_out_at', $payload)
            ) {
                $payload['work_minutes'] = null;
            }

            return;
        }

        $checkInAt = Carbon::parse($checkIn);
        $checkOutAt = Carbon::parse($checkOut);

        $payload['work_minutes'] = (int) floor(($checkOutAt->getTimestamp() - $checkInAt->getTimestamp()) / 60);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function fillAuditColumns(array &$payload, bool $creating = false): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        if ($creating && Schema::hasColumn('attendances', 'created_by')) {
            $payload['created_by'] = $userId;
        }

        if (Schema::hasColumn('attendances', 'updated_by')) {
            $payload['updated_by'] = $userId;
        }
    }
}
