<?php

namespace App\Services;

use App\Models\Payroll;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function create(array $data): Payroll
    {
        return DB::transaction(function () use ($data): Payroll {
            $payload = $this->prepareData($data);

            $this->ensureUniquePayroll($payload);
            $this->calculateNetSalary($payload);

            return Payroll::create($payload);
        });
    }

    public function update(Payroll $payroll, array $data): Payroll
    {
        return DB::transaction(function () use ($payroll, $data): Payroll {
            $payload = $this->prepareData($data);

            $this->ensureUniquePayroll($payload, $payroll);
            $this->calculateNetSalary($payload, $payroll);

            $payroll->update($payload);

            return $payroll->refresh();
        });
    }

    public function delete(Payroll $payroll): bool
    {
        return DB::transaction(fn (): bool => (bool) $payroll->delete());
    }

    private function prepareData(array $data): array
    {
        $payload = Arr::only($data, [
            'payroll_run_id',
            'employee_id',
            'month',
            'year',
            'period_month',
            'period_year',
            'payroll_month',
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
            'status',
            'payment_status',
            'paid_at',
            'payment_date',
        ]);

        $table = $this->payrollTable();

        foreach (array_keys($payload) as $column) {
            if (! Schema::hasColumn($table, $column)) {
                unset($payload[$column]);
            }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function ensureUniquePayroll(array $payload, ?Payroll $payroll = null): void
    {
        $table = $this->payrollTable();

        if (Schema::hasColumn($table, 'employee_id') && Schema::hasColumn($table, 'payroll_run_id')) {
            $employeeId = $payload['employee_id'] ?? $payroll?->employee_id;
            $payrollRunId = $payload['payroll_run_id'] ?? $payroll?->payroll_run_id;

            if (filled($employeeId) && filled($payrollRunId)) {
                $exists = Payroll::query()
                    ->where('employee_id', $employeeId)
                    ->where('payroll_run_id', $payrollRunId)
                    ->when($payroll !== null, fn ($query) => $query->whereKeyNot($payroll->getKey()))
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'employee_id' => 'Payroll already exists for this employee and payroll run.',
                    ]);
                }
            }
        }

        if (
            Schema::hasColumn($table, 'employee_id')
            && Schema::hasColumn($table, 'month')
            && Schema::hasColumn($table, 'year')
        ) {
            $this->ensureUniqueByColumns($payload, ['employee_id', 'month', 'year'], $payroll);
        }

        if (
            Schema::hasColumn($table, 'employee_id')
            && Schema::hasColumn($table, 'period_month')
            && Schema::hasColumn($table, 'period_year')
        ) {
            $this->ensureUniqueByColumns($payload, ['employee_id', 'period_month', 'period_year'], $payroll);
        }

        if (
            Schema::hasColumn($table, 'employee_id')
            && Schema::hasColumn($table, 'payroll_month')
        ) {
            $this->ensureUniqueByColumns($payload, ['employee_id', 'payroll_month'], $payroll);
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $columns
     */
    private function ensureUniqueByColumns(array $payload, array $columns, ?Payroll $payroll = null): void
    {
        $query = Payroll::query();

        foreach ($columns as $column) {
            $value = $payload[$column] ?? $payroll?->{$column};

            if (blank($value)) {
                return;
            }

            $query->where($column, $value);
        }

        $exists = $query
            ->when($payroll !== null, fn ($query) => $query->whereKeyNot($payroll->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'employee_id' => 'Payroll already exists for this employee and pay period.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function calculateNetSalary(array &$payload, ?Payroll $payroll = null): void
    {
        $table = $this->payrollTable();

        if (! Schema::hasColumn($table, 'net_salary')) {
            return;
        }

        if (Schema::hasColumn($table, 'gross_salary') && Schema::hasColumn($table, 'total_deductions')) {
            $grossSalary = $this->amount($payload['gross_salary'] ?? $payroll?->gross_salary);
            $deductions = $this->amount($payload['total_deductions'] ?? $payroll?->total_deductions);

            $payload['net_salary'] = $grossSalary - $deductions;

            return;
        }

        $salaryColumn = $this->firstExistingColumn(['basic_salary', 'salary']);
        $allowanceColumn = $this->firstExistingColumn(['allowance', 'allowances', 'total_allowances']);
        $deductionColumn = $this->firstExistingColumn(['deduction', 'deductions', 'total_deductions']);

        if ($salaryColumn === null) {
            return;
        }

        $salary = $this->amount($payload[$salaryColumn] ?? $payroll?->{$salaryColumn});
        $allowances = $allowanceColumn === null
            ? 0.0
            : $this->amount($payload[$allowanceColumn] ?? $payroll?->{$allowanceColumn});
        $deductions = $deductionColumn === null
            ? 0.0
            : $this->amount($payload[$deductionColumn] ?? $payroll?->{$deductionColumn});

        $payload['net_salary'] = $salary + $allowances - $deductions;
    }

    /**
     * @param list<string> $columns
     */
    private function firstExistingColumn(array $columns): ?string
    {
        $table = $this->payrollTable();

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function amount(mixed $value): float
    {
        return (float) ($value ?? 0);
    }

    private function payrollTable(): string
    {
        return (new Payroll())->getTable();
    }
}
