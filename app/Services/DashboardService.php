<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'total_departments' => $this->countModel(Department::class),
            'total_designations' => $this->countModel(Designation::class),
            'total_employees' => $this->countModel(Employee::class),
            'today_attendance_count' => $this->todayAttendanceCount(),
            'pending_leave_requests_count' => $this->pendingLeaveRequestsCount(),
            'current_month_payrolls_count' => $this->currentMonthPayrollsCount(),
            'recent_employees' => $this->recentRecords(Employee::class, ['user', 'department', 'designation']),
            'recent_leave_requests' => $this->recentRecords(LeaveRequest::class, ['employee.user', 'approver']),
            'recent_payrolls' => $this->recentRecords(Payroll::class, ['employee.user']),
        ];
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function countModel(string $modelClass): int
    {
        if (! $this->modelHasTable($modelClass)) {
            return 0;
        }

        return $modelClass::query()->count();
    }

    private function todayAttendanceCount(): int
    {
        if (! $this->modelHasTable(Attendance::class)) {
            return 0;
        }

        $table = $this->tableFor(Attendance::class);
        $dateColumn = $this->firstExistingColumn($table, ['attendance_date', 'date']);

        if ($dateColumn === null) {
            return 0;
        }

        return Attendance::query()
            ->whereDate($dateColumn, today()->toDateString())
            ->count();
    }

    private function pendingLeaveRequestsCount(): int
    {
        if (! $this->modelHasTable(LeaveRequest::class)) {
            return 0;
        }

        $table = $this->tableFor(LeaveRequest::class);

        if (! Schema::hasColumn($table, 'status')) {
            return 0;
        }

        return LeaveRequest::query()
            ->where('status', 'pending')
            ->count();
    }

    private function currentMonthPayrollsCount(): int
    {
        if (! $this->modelHasTable(Payroll::class)) {
            return 0;
        }

        $table = $this->tableFor(Payroll::class);
        $now = today();

        if (Schema::hasColumn($table, 'payroll_month')) {
            return Payroll::query()
                ->where('payroll_month', $now->format('Y-m'))
                ->count();
        }

        $monthColumn = $this->firstExistingColumn($table, ['month', 'period_month']);
        $yearColumn = $this->firstExistingColumn($table, ['year', 'period_year']);

        if ($monthColumn !== null && $yearColumn !== null) {
            return Payroll::query()
                ->where($monthColumn, (int) $now->format('n'))
                ->where($yearColumn, (int) $now->format('Y'))
                ->count();
        }

        if (
            Schema::hasColumn($table, 'payroll_run_id')
            && Schema::hasTable('payroll_runs')
            && Schema::hasColumn('payroll_runs', 'period_month')
            && Schema::hasColumn('payroll_runs', 'period_year')
        ) {
            return DB::table($table)
                ->join('payroll_runs', "{$table}.payroll_run_id", '=', 'payroll_runs.id')
                ->where('payroll_runs.period_month', (int) $now->format('n'))
                ->where('payroll_runs.period_year', (int) $now->format('Y'))
                ->count();
        }

        return 0;
    }

    /**
     * @param class-string<Model> $modelClass
     * @param list<string> $relationships
     */
    private function recentRecords(string $modelClass, array $relationships = []): Collection
    {
        if (! $this->modelHasTable($modelClass)) {
            return collect();
        }

        $query = $modelClass::query();
        $relationships = array_values(array_filter(
            $relationships,
            fn (string $relationship): bool => $this->relationshipExists($modelClass, $relationship),
        ));

        if ($relationships !== []) {
            $query->with($relationships);
        }

        return $query
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function relationshipExists(string $modelClass, string $relationship): bool
    {
        $rootRelationship = explode('.', $relationship, 2)[0];

        return method_exists($modelClass, $rootRelationship);
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
}
