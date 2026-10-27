<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function create(array $data): LeaveRequest
    {
        return DB::transaction(function () use ($data): LeaveRequest {
            $payload = $this->prepareData($data);

            $this->fillTotalDays($payload);
            $this->ensureNoOverlap($payload);

            return LeaveRequest::create($payload);
        });
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $data): LeaveRequest {
            $this->ensurePending($leaveRequest);
            $payload = $this->prepareData($data);

            $this->fillTotalDays($payload, $leaveRequest);
            $this->ensureNoOverlap($payload, $leaveRequest);

            $leaveRequest->update($payload);

            return $leaveRequest->refresh();
        });
    }

    public function delete(LeaveRequest $leaveRequest): bool
    {
        return DB::transaction(function () use ($leaveRequest): bool {
            if ($leaveRequest->status === 'approved') {
                $this->restoreLeaveBalance($leaveRequest);
            }

            return (bool) $leaveRequest->delete();
        });
    }

    public function approve(LeaveRequest $leaveRequest): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest): LeaveRequest {
            $this->ensurePending($leaveRequest);

            if (auth()->id() !== null && $leaveRequest->employee?->user_id === auth()->id()) {
                throw new DomainException('Employees cannot approve their own leave requests.');
            }

            $this->applyLeaveBalance($leaveRequest);
            $payload = [];

            if (Schema::hasColumn('leave_requests', 'status')) {
                $payload['status'] = 'approved';
            }

            $this->fillReviewColumns($payload, 'approved');

            if (Schema::hasColumn('leave_requests', 'rejection_reason')) {
                $payload['rejection_reason'] = null;
            }

            if ($payload !== []) {
                $leaveRequest->update($payload);
            }

            return $leaveRequest->refresh();
        });
    }

    public function reject(LeaveRequest $leaveRequest): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest): LeaveRequest {
            $this->ensurePending($leaveRequest);
            $payload = [];

            if (Schema::hasColumn('leave_requests', 'status')) {
                $payload['status'] = 'rejected';
            }

            $this->fillReviewColumns($payload, 'rejected');

            if ($payload !== []) {
                $leaveRequest->update($payload);
            }

            return $leaveRequest->refresh();
        });
    }

    private function prepareData(array $data): array
    {
        $payload = Arr::only($data, [
            'employee_id',
            'leave_type_id',
            'start_date',
            'end_date',
            'total_days',
            'reason',
            'status',
            'rejection_reason',
            'remarks',
        ]);

        foreach (array_keys($payload) as $column) {
            if (! Schema::hasColumn('leave_requests', $column)) {
                unset($payload[$column]);
            }
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function fillTotalDays(array &$payload, ?LeaveRequest $leaveRequest = null): void
    {
        if (! Schema::hasColumn('leave_requests', 'total_days')) {
            return;
        }

        if (filled($payload['total_days'] ?? null)) {
            return;
        }

        $startDate = $payload['start_date'] ?? $leaveRequest?->start_date;
        $endDate = $payload['end_date'] ?? $leaveRequest?->end_date;

        if (blank($startDate) || blank($endDate)) {
            return;
        }

        $payload['total_days'] = Carbon::parse($startDate)
            ->startOfDay()
            ->diffInDays(Carbon::parse($endDate)->startOfDay()) + 1;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ensureNoOverlap(array $payload, ?LeaveRequest $leaveRequest = null): void
    {
        if (
            ! Schema::hasColumn('leave_requests', 'employee_id')
            || ! Schema::hasColumn('leave_requests', 'start_date')
            || ! Schema::hasColumn('leave_requests', 'end_date')
        ) {
            return;
        }

        $employeeId = $payload['employee_id'] ?? $leaveRequest?->employee_id;
        $startDate = $payload['start_date'] ?? $leaveRequest?->start_date;
        $endDate = $payload['end_date'] ?? $leaveRequest?->end_date;

        if (blank($employeeId) || blank($startDate) || blank($endDate)) {
            return;
        }

        $exists = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->when(
                Schema::hasColumn('leave_requests', 'status'),
                fn ($query) => $query->whereNotIn('status', ['rejected', 'cancelled']),
            )
            ->when($leaveRequest !== null, fn ($query) => $query->whereKeyNot($leaveRequest->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_date' => 'A leave request already exists for this employee during the selected date range.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function fillReviewColumns(array &$payload, string $action): void
    {
        $userId = auth()->id();
        $now = now();

        if ($userId !== null) {
            if ($action === 'approved' && Schema::hasColumn('leave_requests', 'approved_by')) {
                $payload['approved_by'] = $userId;
            }

            if ($action === 'rejected' && Schema::hasColumn('leave_requests', 'rejected_by')) {
                $payload['rejected_by'] = $userId;
            }

            if (Schema::hasColumn('leave_requests', 'reviewed_by')) {
                $payload['reviewed_by'] = $userId;
            }
        }

        if ($action === 'approved' && Schema::hasColumn('leave_requests', 'approved_at')) {
            $payload['approved_at'] = $now;
        }

        if ($action === 'rejected' && Schema::hasColumn('leave_requests', 'rejected_at')) {
            $payload['rejected_at'] = $now;
        }

        if (Schema::hasColumn('leave_requests', 'reviewed_at')) {
            $payload['reviewed_at'] = $now;
        }
    }

    private function ensurePending(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->status !== 'pending') {
            throw new DomainException('Only pending leave requests can be reviewed or edited.');
        }
    }

    private function applyLeaveBalance(LeaveRequest $leaveRequest): void
    {
        if (! Schema::hasTable('leave_balances') || ! Schema::hasTable('leave_types')) {
            return;
        }

        foreach ($this->daysByYear($leaveRequest) as $year => $days) {
            $balance = DB::table('leave_balances')
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($balance === null) {
                $allocatedDays = (float) DB::table('leave_types')
                    ->where('id', $leaveRequest->leave_type_id)
                    ->value('default_days');

                DB::table('leave_balances')->insert([
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'year' => $year,
                    'allocated_days' => $allocatedDays,
                    'used_days' => 0,
                    'remaining_days' => $allocatedDays,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $balance = DB::table('leave_balances')
                    ->where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->first();
            }

            if ((float) $balance->remaining_days < $days) {
                throw new DomainException("Insufficient leave balance for {$year}.");
            }

            DB::table('leave_balances')
                ->where('id', $balance->id)
                ->update([
                    'used_days' => (float) $balance->used_days + $days,
                    'remaining_days' => (float) $balance->remaining_days - $days,
                    'updated_at' => now(),
                ]);
        }
    }

    private function restoreLeaveBalance(LeaveRequest $leaveRequest): void
    {
        if (! Schema::hasTable('leave_balances')) {
            return;
        }

        foreach ($this->daysByYear($leaveRequest) as $year => $days) {
            $balance = DB::table('leave_balances')
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($balance === null) {
                continue;
            }

            DB::table('leave_balances')
                ->where('id', $balance->id)
                ->update([
                    'used_days' => max(0, (float) $balance->used_days - $days),
                    'remaining_days' => min(
                        (float) $balance->allocated_days,
                        (float) $balance->remaining_days + $days,
                    ),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * @return array<int, float>
     */
    private function daysByYear(LeaveRequest $leaveRequest): array
    {
        $daysByYear = [];
        $cursor = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $endDate = Carbon::parse($leaveRequest->end_date)->startOfDay();

        while ($cursor->lte($endDate)) {
            $yearEnd = $cursor->copy()->endOfYear();
            $segmentEnd = $yearEnd->lt($endDate) ? $yearEnd : $endDate;
            $daysByYear[$cursor->year] = (float) ($cursor->diffInDays($segmentEnd) + 1);
            $cursor = $segmentEnd->copy()->addDay();
        }

        return $daysByYear;
    }
}
