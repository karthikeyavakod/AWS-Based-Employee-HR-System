<?php

namespace App\Http\Requests\Admin\Payroll;

use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class UpdatePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('payroll.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->payrollRules();
    }

    /**
     * @return array<string, mixed>
     */
    private function payrollRules(): array
    {
        $table = (new Payroll())->getTable();
        $rules = [];

        if (Schema::hasColumn($table, 'payroll_run_id')) {
            $rules['payroll_run_id'] = ['required', 'integer', 'exists:payroll_runs,id'];
        }

        if (Schema::hasColumn($table, 'employee_id')) {
            $rules['employee_id'] = ['required', 'integer', 'exists:employees,id'];
        }

        foreach (['month', 'period_month'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $rules[$column] = ['required', 'integer', 'between:1,12'];
            }
        }

        foreach (['year', 'period_year'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $rules[$column] = ['required', 'integer', 'digits:4'];
            }
        }

        if (Schema::hasColumn($table, 'payroll_month')) {
            $rules['payroll_month'] = ['required', 'date_format:Y-m'];
        }

        foreach ($this->numericColumns() as $column) {
            if (Schema::hasColumn($table, $column)) {
                $rules[$column] = ['nullable', 'numeric', 'min:0'];
            }
        }

        foreach (['status', 'payment_status'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $rules[$column] = ['nullable', 'string', 'max:255'];
            }
        }

        foreach (['paid_at', 'payment_date'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $rules[$column] = ['nullable', 'date'];
            }
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    private function numericColumns(): array
    {
        return [
            'basic_salary',
            'salary',
            'gross_salary',
            'allowance',
            'allowances',
            'total_allowances',
            'deduction',
            'deductions',
            'total_deductions',
            'net_salary',
        ];
    }
}
