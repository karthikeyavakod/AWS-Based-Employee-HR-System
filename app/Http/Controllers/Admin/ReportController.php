<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report\ReportFilterRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\ReportService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function employees(ReportFilterRequest $request, ReportService $reportService): View
    {
        $filters = $request->validated();
        $employees = $reportService->employeeReport($filters);
        $departments = $this->departments();
        $designations = $this->designations();

        return view('admin.reports.employees', compact(
            'employees',
            'filters',
            'departments',
            'designations',
        ));
    }

    public function attendance(ReportFilterRequest $request, ReportService $reportService): View
    {
        $filters = $request->validated();
        $attendances = $reportService->attendanceReport($filters);
        $employees = $this->employeesForFilters();

        return view('admin.reports.attendance', compact(
            'attendances',
            'filters',
            'employees',
        ));
    }

    public function leaves(ReportFilterRequest $request, ReportService $reportService): View
    {
        $filters = $request->validated();
        $leaveRequests = $reportService->leaveReport($filters);
        $employees = $this->employeesForFilters();

        return view('admin.reports.leaves', compact(
            'leaveRequests',
            'filters',
            'employees',
        ));
    }

    public function payrolls(ReportFilterRequest $request, ReportService $reportService): View
    {
        $filters = $request->validated();
        $payrolls = $reportService->payrollReport($filters);
        $employees = $this->employeesForFilters();
        $payrollRunsById = $this->payrollRuns()->keyBy('id');

        return view('admin.reports.payrolls', compact(
            'payrolls',
            'filters',
            'employees',
            'payrollRunsById',
        ));
    }

    private function departments(): Collection
    {
        if (! $this->modelHasTable(Department::class)) {
            return collect();
        }

        return $this->orderedLookup(Department::class, ['name', 'code']);
    }

    private function designations(): Collection
    {
        if (! $this->modelHasTable(Designation::class)) {
            return collect();
        }

        return $this->orderedLookup(Designation::class, ['name', 'code']);
    }

    private function employeesForFilters(): Collection
    {
        if (! $this->modelHasTable(Employee::class)) {
            return collect();
        }

        $table = $this->tableFor(Employee::class);
        $query = Employee::query();

        if (method_exists(Employee::class, 'user')) {
            $query->with('user');
        }

        foreach (['first_name', 'last_name', 'employee_code', 'id'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $query->orderBy($column);
            }
        }

        return $query->get();
    }

    private function payrollRuns(): Collection
    {
        if (! Schema::hasTable('payroll_runs')) {
            return collect();
        }

        $columns = collect(['id', 'title', 'period_year', 'period_month', 'period_start', 'period_end', 'status', 'payment_date'])
            ->filter(fn (string $column): bool => Schema::hasColumn('payroll_runs', $column))
            ->values()
            ->all();

        if ($columns === []) {
            return collect();
        }

        return DB::table('payroll_runs')
            ->select($columns)
            ->orderByDesc(Schema::hasColumn('payroll_runs', 'period_year') ? 'period_year' : 'id')
            ->when(
                Schema::hasColumn('payroll_runs', 'period_month'),
                fn ($query) => $query->orderByDesc('period_month'),
            )
            ->get();
    }

    /**
     * @param class-string<Model> $modelClass
     * @param list<string> $orderColumns
     */
    private function orderedLookup(string $modelClass, array $orderColumns): Collection
    {
        $table = $this->tableFor($modelClass);
        $query = $modelClass::query();

        foreach ($orderColumns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $query->orderBy($column);
            }
        }

        return $query->get();
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
}
