<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Carbon\Carbon;
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
            $payload = $this->prepareData($data);

            $this->fillTotalDays($payload, $leaveRequest);
            $this->ensureNoOverlap($payload, $leaveRequest);

            $leaveRequest->update($payload);

            return $leaveRequest->refresh();
        });
    }

    public function delete(LeaveRequest $leaveRequest): bool
    {
        return DB::transaction(fn (): bool => (bool) $leaveRequest->delete());
    }

    public function approve(LeaveRequest $leaveRequest): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest): LeaveRequest {
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
     * @param array<string, mixed> $payload
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
     * @param array<string, mixed> $payload
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
            ->when($leaveRequest !== null, fn ($query) => $query->whereKeyNot($leaveRequest->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_date' => 'A leave request already exists for this employee during the selected date range.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
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
}
