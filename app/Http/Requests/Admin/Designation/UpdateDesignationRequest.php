<?php

namespace App\Http\Requests\Admin\Designation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('designation.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $designation = $this->route('designation');
        $rules = [];

        if (Schema::hasColumn('designations', 'department_id')) {
            $rules['department_id'] = ['required', 'integer', 'exists:departments,id'];
        }

        if (Schema::hasColumn('designations', 'name')) {
            $rules['name'] = $this->nameRules($designation?->getKey());
        }

        if (Schema::hasColumn('designations', 'code')) {
            $rules['code'] = [
                'nullable',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('designations', 'code')->ignore($designation?->getKey()),
            ];
        }

        if (Schema::hasColumn('designations', 'slug')) {
            $rules['slug'] = [
                'nullable',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('designations', 'slug')->ignore($designation?->getKey()),
            ];
        }

        if (Schema::hasColumn('designations', 'description')) {
            $rules['description'] = ['nullable', 'string'];
        }

        if (Schema::hasColumn('designations', 'status')) {
            $rules['status'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return list<mixed>
     */
    private function nameRules(mixed $ignoreId): array
    {
        $rules = ['required', 'string', 'max:255'];

        if (Schema::hasColumn('designations', 'department_id')) {
            $rules[] = Rule::unique('designations', 'name')
                ->where(fn ($query) => $query->where('department_id', $this->input('department_id')))
                ->ignore($ignoreId);

            return $rules;
        }

        $rules[] = Rule::unique('designations', 'name')->ignore($ignoreId);

        return $rules;
    }
}
