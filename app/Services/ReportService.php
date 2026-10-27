<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    private const DEFAULT_LIMIT = 100;

    /**
     * @param array<string, mixed> $filters
     */
    public function employeeReport(array $filters = []): Collection
    {
        if (! $this->modelHasTable(Employee::class)) {
            return collect();
        }

        $table = $this->tableFor(Employee::class);
        $query = Employee::query()->select("{$table}.*");

        $this->withExistingRelationships($query, ['user', 'department', 'designation']);
        $this->whereColumnFilter($query, $table, 'department_id', $filters['department_id'] ?? null);
        $this->whereColumnFilter($query, $table, 'designation_id', $filters['designation_id'] ?? null);
        $this->whereColumnFilter($query, $table, 'status', $filters['status'] ?? null);

        return $this->limitedResults($query, $table);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function attendanceReport(array $filters = []): Collection
    {
        if (! $this->modelHasTable(Attendance::class)) {
            return collect();
        }

        $table = $this->tableFor(Attendance::class);
        $dateColumn = $this->firstExistingColumn($table, ['attendance_date', 'date']);
        $query = Attendance::query()->select("{$table}.*");

        $this->withExistingRelationships($query, $this->employeeNestedRelationships(Attendance::class));
        $this->whereColumnFilter($query, $table, 'employee_id', $filters['employee_id'] ?? null);
        $this->whereColumnFilter($query, $table, 'status', $filters['status'] ?? null);
        $this->whereDateFilter($query, $table, $dateColumn, '>=', $filters['from_date'] ?? null);
        $this->whereDateFilter($query, $table, $dateColumn, '<=', $filters['to_date'] ?? null);

        return $this->limitedResults($query, $table);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function leaveReport(array $filters = []): Collection
    {
        if (! $this->modelHasTable(LeaveRequest::class)) {
            return collect();
        }

        $table = $this->tableFor(LeaveRequest::class);
        $startDateColumn = $this->firstExistingColumn($table, ['start_date', 'from_date']);
        $endDateColumn = $this->firstExistingColumn($table, ['end_date', 'to_date']);
        $query = LeaveRequest::query()->select("{$table}.*");

        $this->withExistingRelationships($query, array_merge(
            $this->employeeNestedRelationships(LeaveRequest::class),
            method_exists(LeaveRequest::class, 'approver') ? ['approver'] : [],
        ));
        $this->whereColumnFilter($query, $table, 'employee_id', $filters['employee_id'] ?? null);
        $this->whereColumnFilter($query, $table, 'status', $filters['status'] ?? null);

        if ($this->filled($filters['from_date'] ?? null)) {
            $this->whereDateFilter($query, $table, $endDateColumn ?? $startDateColumn, '>=', $filters['from_date']);
        }

        if ($this->filled($filters['to_date'] ?? null)) {
            $this->whereDateFilter($query, $table, $startDateColumn ?? $endDateColumn, '<=', $filters['to_date']);
        }

        return $this->limitedResults($query, $table);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function payrollReport(array $filters = []): Collection
    {
        if (! $this->modelHasTable(Payroll::class)) {
            return collect();
        }

        $table = $this->tableFor(Payroll::class);
        $query = Payroll::query()->select("{$table}.*");
        $payrollRunsJoined = false;

        $this->withExistingRelationships($query, $this->employeeNestedRelationships(Payroll::class));
        $this->whereColumnFilter($query, $table, 'employee_id', $filters['employee_id'] ?? null);

        $payrollMonth = $filters['payroll_month'] ?? null;
        $month = $filters['month'] ?? $this->monthFromPayrollMonth($payrollMonth);
        $year = $filters['year'] ?? $this->yearFromPayrollMonth($payrollMonth);

        $this->applyPayrollMonthFilter($query, $table, $month, $payrollMonth, $payrollRunsJoined);
        $this->applyPayrollYearFilter($query, $table, $year, $payrollRunsJoined);

        $status = $filters['payment_status'] ?? $filters['status'] ?? null;
        $statusColumn = $this->firstExistingColumn($table, ['payment_status', 'status']);
        $this->whereColumnFilter($query, $table, $statusColumn, $status);

        return $this->limitedResults($query, $table);
    }

    private function applyPayrollMonthFilter(
        Builder $query,
        string $table,
        mixed $month,
        mixed $payrollMonth,
        bool &$payrollRunsJoined
    ): void {
        if (Schema::hasColumn($table, 'payroll_month')) {
            $value = $this->filled($payrollMonth) ? $payrollMonth : $month;

            if ($this->filled($value)) {
                $query->where($this->qualifiedColumn($table, 'payroll_month'), $value);
            }

            return;
        }

        if (! $this->filled($month)) {
            return;
        }

        $payrollMonthColumn = $this->firstExistingColumn($table, ['month', 'period_month']);

        if ($payrollMonthColumn !== null) {
            $query->where($this->qualifiedColumn($table, $payrollMonthColumn), $month);

            return;
        }

        if ($this->canJoinPayrollRuns($table, 'period_month')) {
            $this->joinPayrollRuns($query, $table, $payrollRunsJoined);
            $query->where('payroll_runs.period_month', $month);
        }
    }

    private function applyPayrollYearFilter(
        Builder $query,
        string $table,
        mixed $year,
        bool &$payrollRunsJoined
    ): void {
        if (! $this->filled($year)) {
            return;
        }

        $yearColumn = $this->firstExistingColumn($table, ['year', 'period_year']);

        if ($yearColumn !== null) {
            $query->where($this->qualifiedColumn($table, $yearColumn), $year);

            return;
        }

        if ($this->canJoinPayrollRuns($table, 'period_year')) {
            $this->joinPayrollRuns($query, $table, $payrollRunsJoined);
            $query->where('payroll_runs.period_year', $year);
        }
    }

    private function canJoinPayrollRuns(string $table, string $payrollRunColumn): bool
    {
        return Schema::hasColumn($table, 'payroll_run_id')
            && Schema::hasTable('payroll_runs')
            && Schema::hasColumn('payroll_runs', $payrollRunColumn);
    }

    private function joinPayrollRuns(Builder $query, string $table, bool &$payrollRunsJoined): void
    {
        if ($payrollRunsJoined) {
            return;
        }

        $query->join('payroll_runs', "{$table}.payroll_run_id", '=', 'payroll_runs.id');
        $payrollRunsJoined = true;
    }

    /**
     * @param list<string> $relationships
     */
    private function withExistingRelationships(Builder $query, array $relationships): void
    {
        $relationships = array_values(array_filter(
            $relationships,
            fn (string $relationship): bool => $this->relationshipExists(get_class($query->getModel()), $relationship),
        ));

        if ($relationships !== []) {
            $query->with($relationships);
        }
    }

    /**
     * @param class-string<Model> $modelClass
     *
     * @return list<string>
     */
    private function employeeNestedRelationships(string $modelClass): array
    {
        if (! method_exists($modelClass, 'employee')) {
            return [];
        }

        return method_exists(Employee::class, 'user')
            ? ['employee.user']
            : ['employee'];
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function relationshipExists(string $modelClass, string $relationship): bool
    {
        $rootRelationship = explode('.', $relationship, 2)[0];

        return method_exists($modelClass, $rootRelationship);
    }

    private function whereColumnFilter(Builder $query, string $table, ?string $column, mixed $value): void
    {
        if ($column === null || ! $this->filled($value) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $query->where($this->qualifiedColumn($table, $column), $value);
    }

    private function whereDateFilter(
        Builder $query,
        string $table,
        ?string $column,
        string $operator,
        mixed $value
    ): void {
        if ($column === null || ! $this->filled($value) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $query->whereDate($this->qualifiedColumn($table, $column), $operator, $value);
    }

    private function limitedResults(Builder $query, string $table): Collection
    {
        if (Schema::hasColumn($table, 'created_at')) {
            $query->orderByDesc($this->qualifiedColumn($table, 'created_at'));
        } elseif (Schema::hasColumn($table, 'id')) {
            $query->orderByDesc($this->qualifiedColumn($table, 'id'));
        }

        return $query
            ->limit(self::DEFAULT_LIMIT)
            ->get();
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function modelHasTable(string $modelClass): bool
    {
        return class_exists($modelClass) && Schema::hasTable($this->tableFor($modelClass));
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function tableFor(string $modelClass): string
    {
        return (new $modelClass())->getTable();
    }

    /**
     * @param list<string> $columns
     */
    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function qualifiedColumn(string $table, string $column): string
    {
        return "{$table}.{$column}";
    }

    private function monthFromPayrollMonth(mixed $payrollMonth): mixed
    {
        if (! is_string($payrollMonth) || ! str_contains($payrollMonth, '-')) {
            return $payrollMonth;
        }

        return (int) substr($payrollMonth, -2);
    }

    private function yearFromPayrollMonth(mixed $payrollMonth): mixed
    {
        if (! is_string($payrollMonth) || ! str_contains($payrollMonth, '-')) {
            return null;
        }

        return (int) substr($payrollMonth, 0, 4);
    }

    private function filled(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}
