<?php

namespace App\Http\Requests\Admin\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('department.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $department = $this->route('department');
        $rules = [];

        if (Schema::hasColumn('departments', 'name')) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($department?->getKey()),
            ];
        }

        if (Schema::hasColumn('departments', 'code')) {
            $rules['code'] = [
                'nullable',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('departments', 'code')->ignore($department?->getKey()),
            ];
        }

        if (Schema::hasColumn('departments', 'slug')) {
            $rules['slug'] = [
                'nullable',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('departments', 'slug')->ignore($department?->getKey()),
            ];
        }

        if (Schema::hasColumn('departments', 'description')) {
            $rules['description'] = ['nullable', 'string'];
        }

        if (Schema::hasColumn('departments', 'status')) {
            $rules['status'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
