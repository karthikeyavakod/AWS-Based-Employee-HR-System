<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payroll\StorePayrollRequest;
use App\Http\Requests\Admin\Payroll\UpdatePayrollRequest;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(): View
    {
        $payrolls = Payroll::query()
            ->with($this->payrollRelationships())
            ->latest()
            ->paginate(15);

        $payrollRunsById = $this->payrollRuns()->keyBy('id');

        return view('admin.payrolls.index', compact('payrolls', 'payrollRunsById'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->when(method_exists(Employee::class, 'user'), fn ($query) => $query->with('user'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $payrollRuns = $this->payrollRuns();

        return view('admin.payrolls.create', compact('employees', 'payrollRuns'));
    }

    public function store(StorePayrollRequest $request, PayrollService $payrollService): RedirectResponse
    {
        $payrollService->create($request->validated());

        return redirect()
            ->route('admin.payrolls.index')
            ->with('success', 'Payroll record created successfully.');
    }

    public function edit(Payroll $payroll): View
    {
        $payroll->load($this->payrollRelationships());

        $employees = Employee::query()
            ->when(method_exists(Employee::class, 'user'), fn ($query) => $query->with('user'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $payrollRuns = $this->payrollRuns();

        return view('admin.payrolls.edit', compact('payroll', 'employees', 'payrollRuns'));
    }

    public function update(
        UpdatePayrollRequest $request,
        Payroll $payroll,
        PayrollService $payrollService
    ): RedirectResponse {
        $payrollService->update($payroll, $request->validated());

        return redirect()
            ->route('admin.payrolls.edit', $payroll)
            ->with('success', 'Payroll record updated successfully.');
    }

    public function destroy(Payroll $payroll, PayrollService $payrollService): RedirectResponse
    {
        try {
            $payrollService->delete($payroll);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.payrolls.index')
            ->with('success', 'Payroll record deleted successfully.');
    }

    /**
     * @return list<string>
     */
    private function payrollRelationships(): array
    {
        if (! method_exists(Payroll::class, 'employee')) {
            return [];
        }

        return method_exists(Employee::class, 'user')
            ? ['employee.user']
            : ['employee'];
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

        return DB::table('payroll_runs')
            ->select($columns)
            ->orderByDesc(Schema::hasColumn('payroll_runs', 'period_year') ? 'period_year' : 'id')
            ->when(
                Schema::hasColumn('payroll_runs', 'period_month'),
                fn ($query) => $query->orderByDesc('period_month'),
            )
            ->get();
    }
}
