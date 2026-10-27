<?php

namespace App\Http\Requests\Admin\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('leave.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];
        $user = $this->user();
        $isEmployee = $user?->hasRole('employee')
            && ! $user->hasAnyRole(['super-admin', 'hr-manager']);
        $employee = $isEmployee ? $user->employee : null;

        if (Schema::hasColumn('leave_requests', 'employee_id')) {
            $rules['employee_id'] = match (true) {
                $isEmployee && $employee !== null => ['required', 'integer', Rule::in([$employee->id])],
                $isEmployee => ['prohibited'],
                default => ['required', 'integer', 'exists:employees,id'],
            };
        }

        if (Schema::hasColumn('leave_requests', 'leave_type_id')) {
            $rules['leave_type_id'] = ['required', 'integer', 'exists:leave_types,id'];
        }

        if (Schema::hasColumn('leave_requests', 'start_date')) {
            $rules['start_date'] = ['required', 'date'];
        }

        if (Schema::hasColumn('leave_requests', 'end_date')) {
            $rules['end_date'] = ['required', 'date'];

            if (Schema::hasColumn('leave_requests', 'start_date')) {
                $rules['end_date'][] = 'after_or_equal:start_date';
            }
        }

        if (Schema::hasColumn('leave_requests', 'total_days')) {
            $rules['total_days'] = $isEmployee ? ['prohibited'] : ['nullable', 'numeric', 'min:0.5'];
        }

        if (Schema::hasColumn('leave_requests', 'reason')) {
            $rules['reason'] = ['nullable', 'string'];
        }

        if (Schema::hasColumn('leave_requests', 'status')) {
            $rules['status'] = $isEmployee ? ['prohibited'] : ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('leave_requests', 'remarks')) {
            $rules['remarks'] = ['nullable', 'string'];
        }

        if (Schema::hasColumn('leave_requests', 'rejection_reason')) {
            $rules['rejection_reason'] = $isEmployee ? ['prohibited'] : ['nullable', 'string'];
        }

        return $rules;
    }
}
