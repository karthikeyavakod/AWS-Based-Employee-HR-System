<?php

namespace App\Http\Requests\Admin\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('report.view');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department_id' => $this->nullableExistsRules('departments'),
            'designation_id' => $this->nullableExistsRules('designations'),
            'employee_id' => $this->nullableExistsRules('employees'),
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'string', 'max:50'],
            'month' => ['nullable', 'max:20'],
            'payroll_month' => ['nullable', 'max:20'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'payment_status' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return list<string>
     */
    private function nullableExistsRules(string $table): array
    {
        $rules = ['nullable', 'integer'];

        if (Schema::hasTable($table)) {
            $rules[] = "exists:{$table},id";
        }

        return $rules;
    }
}
