<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('employee.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        if (Schema::hasColumn('employees', 'user_id')) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')];
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        if (Schema::hasColumn('employees', 'department_id')) {
            $rules['department_id'] = ['required', 'integer', 'exists:departments,id'];
        }

        if (Schema::hasColumn('employees', 'designation_id')) {
            $rules['designation_id'] = ['required', 'integer', 'exists:designations,id'];
        }

        if (Schema::hasColumn('employees', 'employee_code')) {
            $rules['employee_code'] = ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('employees', 'employee_code')];
        }

        if (Schema::hasColumn('employees', 'code')) {
            $rules['code'] = ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('employees', 'code')];
        }

        if (Schema::hasColumn('employees', 'slug')) {
            $rules['slug'] = ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('employees', 'slug')];
        }

        if (Schema::hasColumn('employees', 'first_name')) {
            $rules['first_name'] = ['required', 'string', 'max:255'];
        }

        if (Schema::hasColumn('employees', 'last_name')) {
            $rules['last_name'] = ['required', 'string', 'max:255'];
        }

        if (Schema::hasColumn('employees', 'phone')) {
            $rules['phone'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('employees', 'gender')) {
            $rules['gender'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('employees', 'date_of_birth')) {
            $rules['date_of_birth'] = ['nullable', 'date'];
        }

        if (Schema::hasColumn('employees', 'joining_date')) {
            $rules['joining_date'] = ['required', 'date'];
        }

        if (Schema::hasColumn('employees', 'address')) {
            $rules['address'] = ['nullable', 'string'];
        }

        if (Schema::hasColumn('employees', 'employment_type')) {
            $rules['employment_type'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('employees', 'status')) {
            $rules['status'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
