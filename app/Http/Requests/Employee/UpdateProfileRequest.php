<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user?->hasRole('employee')
            && ! $user->hasAnyRole(['super-admin', 'hr-manager']);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = [];

        if (Schema::hasColumn('employees', 'phone')) {
            $rules['phone'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('employees', 'address')) {
            $rules['address'] = ['nullable', 'string', 'max:5000'];
        }

        foreach (['name', 'email', 'role_id', 'status', 'department_id', 'designation_id', 'employee_code'] as $protectedField) {
            $rules[$protectedField] = ['prohibited'];
        }

        return $rules;
    }
}
