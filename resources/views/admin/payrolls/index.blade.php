@extends('layouts.app')

@section('title', 'Payrolls')

@section('content')
    @php
        $user = auth()->user();
        $payrollTable = (new App\Models\Payroll())->getTable();
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'employee_id') && method_exists(App\Models\Payroll::class, 'employee');
        $hasPayrollRun = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payroll_run_id');
        $hasPayrollMonth = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payroll_month');
        $hasMonth = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'month') || Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'period_month') || $hasPayrollRun;
        $hasYear = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'year') || Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'period_year') || $hasPayrollRun;
        $salaryColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'basic_salary') ? 'basic_salary' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'salary') ? 'salary' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'gross_salary') ? 'gross_salary' : null));
        $allowanceColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'allowance') ? 'allowance' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'allowances') ? 'allowances' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'total_allowances') ? 'total_allowances' : null));
        $deductionColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'deduction') ? 'deduction' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'deductions') ? 'deductions' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'total_deductions') ? 'total_deductions' : null));
        $hasNetSalary = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'net_salary');
        $statusColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payment_status') ? 'payment_status' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'status') ? 'status' : null);
        $paymentDateColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payment_date') ? 'payment_date' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'paid_at') ? 'paid_at' : null);
        $runHasPaymentDate = Illuminate\Support\Facades\Schema::hasTable('payroll_runs') && Illuminate\Support\Facades\Schema::hasColumn('payroll_runs', 'payment_date');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Payrolls</h1>
            <p class="text-body-secondary mb-0">Manage employee payroll records.</p>
        </div>

        @if ($user?->hasPermission('payroll.create'))
            <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Payroll
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            @if ($hasEmployee)
                                <th scope="col">Employee</th>
                            @endif
                            @if ($hasPayrollMonth || $hasMonth)
                                <th scope="col">{{ $hasPayrollMonth ? 'Payroll Month' : 'Month' }}</th>
                            @endif
                            @if ($hasYear)
                                <th scope="col">Year</th>
                            @endif
                            @if ($salaryColumn)
                                <th scope="col">{{ $salaryColumn === 'gross_salary' ? 'Gross Salary' : ($salaryColumn === 'basic_salary' ? 'Basic Salary' : 'Salary') }}</th>
                            @endif
                            @if ($allowanceColumn)
                                <th scope="col">Allowances</th>
                            @endif
                            @if ($deductionColumn)
                                <th scope="col">Deductions</th>
                            @endif
                            @if ($hasNetSalary)
                                <th scope="col">Net Salary</th>
                            @endif
                            @if ($statusColumn)
                                <th scope="col">{{ $statusColumn === 'payment_status' ? 'Payment Status' : 'Status' }}</th>
                            @endif
                            @if ($paymentDateColumn || ($hasPayrollRun && $runHasPaymentDate))
                                <th scope="col">{{ $paymentDateColumn === 'paid_at' ? 'Paid At' : 'Payment Date' }}</th>
                            @endif
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            @php
                                $employee = $hasEmployee ? $payroll->employee : null;
                                $employeeName = $employee?->user?->name
                                    ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $payrollRun = $hasPayrollRun ? $payrollRunsById->get($payroll->payroll_run_id) : null;
                                $monthValue = $hasPayrollMonth
                                    ? $payroll->payroll_month
                                    : ($payroll->month ?? $payroll->period_month ?? $payrollRun?->period_month ?? null);
                                $yearValue = $payroll->year ?? $payroll->period_year ?? $payrollRun?->period_year ?? null;
                                $paymentDateValue = $paymentDateColumn ? $payroll->{$paymentDateColumn} : ($payrollRun?->payment_date ?? null);
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $payroll->id }}</td>
                                @if ($hasEmployee)
                                    <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasPayrollMonth || $hasMonth)
                                    <td>{{ $monthValue ?: 'N/A' }}</td>
                                @endif
                                @if ($hasYear)
                                    <td>{{ $yearValue ?: 'N/A' }}</td>
                                @endif
                                @if ($salaryColumn)
                                    <td>{{ number_format((float) $payroll->{$salaryColumn}, 2) }}</td>
                                @endif
                                @if ($allowanceColumn)
                                    <td>{{ number_format((float) $payroll->{$allowanceColumn}, 2) }}</td>
                                @endif
                                @if ($deductionColumn)
                                    <td>{{ number_format((float) $payroll->{$deductionColumn}, 2) }}</td>
                                @endif
                                @if ($hasNetSalary)
                                    <td>{{ number_format((float) $payroll->net_salary, 2) }}</td>
                                @endif
                                @if ($statusColumn)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst(str_replace('_', ' ', (string) $payroll->{$statusColumn})) }}
                                        </span>
                                    </td>
                                @endif
                                @if ($paymentDateColumn || ($hasPayrollRun && $runHasPaymentDate))
                                    <td>{{ $paymentDateValue ? \Illuminate\Support\Carbon::parse($paymentDateValue)->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                <td>{{ $payroll->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user?->hasPermission('payroll.update'))
                                            <a href="{{ route('admin.payrolls.edit', $payroll) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('payroll.delete'))
                                            <form method="POST" action="{{ route('admin.payrolls.destroy', $payroll) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ 3 + ($hasEmployee ? 1 : 0) + (($hasPayrollMonth || $hasMonth) ? 1 : 0) + ($hasYear ? 1 : 0) + ($salaryColumn ? 1 : 0) + ($allowanceColumn ? 1 : 0) + ($deductionColumn ? 1 : 0) + ($hasNetSalary ? 1 : 0) + ($statusColumn ? 1 : 0) + (($paymentDateColumn || ($hasPayrollRun && $runHasPaymentDate)) ? 1 : 0) }}"
                                    class="text-center text-body-secondary py-5"
                                >
                                    No payroll records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($payrolls->hasPages())
            <div class="card-footer bg-white">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>
@endsection
