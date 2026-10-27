@extends('layouts.app')

@section('title', 'Payroll Report')

@section('content')
    @php
        $filters = $filters ?? [];
        $employees = collect($employees ?? []);
        $payrollRunsById = collect($payrollRunsById ?? []);
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
            <h1 class="h3 mb-1">Payroll Report</h1>
            <p class="text-body-secondary mb-0">Read-only payroll records filtered by employee, period, year, and status.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Reports
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.payrolls') }}" class="row g-3 align-items-end">
                @if ($employees->isNotEmpty())
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select id="employee_id" name="employee_id" class="form-select">
                            <option value="">All employees</option>
                            @foreach ($employees as $employee)
                                @php
                                    $employeeName = $employee->user?->name ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                @endphp
                                <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>
                                    {{ $employeeName ?: 'Employee #'.$employee->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label for="payroll_month" class="form-label">Payroll Month</label>
                    <input type="month" id="payroll_month" name="payroll_month" value="{{ $filters['payroll_month'] ?? '' }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label for="year" class="form-label">Year</label>
                    <input type="number" id="year" name="year" value="{{ $filters['year'] ?? '' }}" class="form-control" min="2000" max="2100">
                </div>

                @if ($statusColumn)
                    <div class="col-md-2">
                        <label for="payment_status" class="form-label">{{ $statusColumn === 'payment_status' ? 'Payment Status' : 'Status' }}</label>
                        <input type="text" id="payment_status" name="payment_status" value="{{ $filters['payment_status'] ?? $filters['status'] ?? '' }}" class="form-control" placeholder="draft">
                    </div>
                @endif

                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.payrolls') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
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
                                <th scope="col">{{ $salaryColumn === 'gross_salary' ? 'Basic Salary' : ($salaryColumn === 'basic_salary' ? 'Basic Salary' : 'Salary') }}</th>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            @php
                                $employee = $hasEmployee ? $payroll->employee : null;
                                $employeeName = $employee?->user?->name ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $payrollRun = $hasPayrollRun ? $payrollRunsById->get($payroll->payroll_run_id) : null;
                                $monthValue = $hasPayrollMonth
                                    ? $payroll->payroll_month
                                    : ($payroll->month ?? $payroll->period_month ?? $payrollRun?->period_month ?? null);
                                $yearValue = $payroll->year ?? $payroll->period_year ?? $payrollRun?->period_year ?? null;
                                $paymentDateValue = $paymentDateColumn ? $payroll->{$paymentDateColumn} : ($payrollRun?->payment_date ?? null);
                            @endphp
                            <tr>
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
                                    <td>{{ $paymentDateValue ? Illuminate\Support\Carbon::parse($paymentDateValue)->format('M d, Y') : 'N/A' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($hasEmployee ? 1 : 0) + (($hasPayrollMonth || $hasMonth) ? 1 : 0) + ($hasYear ? 1 : 0) + ($salaryColumn ? 1 : 0) + ($allowanceColumn ? 1 : 0) + ($deductionColumn ? 1 : 0) + ($hasNetSalary ? 1 : 0) + ($statusColumn ? 1 : 0) + (($paymentDateColumn || ($hasPayrollRun && $runHasPaymentDate)) ? 1 : 0) }}" class="text-center text-body-secondary py-5">
                                    No payroll records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
