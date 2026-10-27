<?php

namespace App\Http\Requests\Admin\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('attendance.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        if (Schema::hasColumn('attendances', 'employee_id')) {
            $rules['employee_id'] = ['required', 'integer', 'exists:employees,id'];
        }

        if (Schema::hasColumn('attendances', 'attendance_date')) {
            $rules['attendance_date'] = ['required', 'date'];
        }

        if (Schema::hasColumn('attendances', 'check_in_at')) {
            $rules['check_in_at'] = ['nullable', 'date'];
        }

        if (Schema::hasColumn('attendances', 'check_out_at')) {
            $rules['check_out_at'] = ['nullable', 'date'];
        }

        if (Schema::hasColumn('attendances', 'status')) {
            $rules['status'] = ['required', 'string', 'max:255'];
        }

        if (Schema::hasColumn('attendances', 'work_minutes')) {
            $rules['work_minutes'] = ['nullable', 'integer', 'min:0'];
        }

        if (Schema::hasColumn('attendances', 'remarks')) {
            $rules['remarks'] = ['nullable', 'string'];
        }

        return $rules;
    }
}
