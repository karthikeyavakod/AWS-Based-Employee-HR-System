@extends('layouts.app')

@section('title', 'Create Payroll')

@section('content')
    @php
        $payrollTable = (new App\Models\Payroll())->getTable();
        $hasPayrollRun = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payroll_run_id');
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'employee_id');
        $monthColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payroll_month') ? 'payroll_month' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'month') ? 'month' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'period_month') ? 'period_month' : null));
        $yearColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'year') ? 'year' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'period_year') ? 'period_year' : null);
        $salaryColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'basic_salary') ? 'basic_salary' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'salary') ? 'salary' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'gross_salary') ? 'gross_salary' : null));
        $allowanceColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'allowance') ? 'allowance' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'allowances') ? 'allowances' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'total_allowances') ? 'total_allowances' : null));
        $deductionColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'deduction') ? 'deduction' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'deductions') ? 'deductions' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'total_deductions') ? 'total_deductions' : null));
        $hasNetSalary = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'net_salary');
        $statusColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payment_status') ? 'payment_status' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'status') ? 'status' : null);
        $paymentDateColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'payment_date') ? 'payment_date' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'paid_at') ? 'paid_at' : null);
        $noteColumn = Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'remarks') ? 'remarks' : (Illuminate\Support\Facades\Schema::hasColumn($payrollTable, 'note') ? 'note' : null);
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Create Payroll</h1>
            <p class="text-body-secondary mb-0">Create a payroll record for an employee.</p>
        </div>

        <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.payrolls.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    @if ($hasEmployee)
                        <div class="col-12 col-lg-6">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    @php
                                        $employeeName = $employee->user?->name
                                            ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                    @endphp
                                    <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                                        {{ $employeeName ?: 'Employee #'.$employee->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasPayrollRun)
                        <div class="col-12 col-lg-6">
                            <label for="payroll_run_id" class="form-label">Payroll Run <span class="text-danger">*</span></label>
                            <select id="payroll_run_id" name="payroll_run_id" class="form-select @error('payroll_run_id') is-invalid @enderror" required>
                                <option value="">Select payroll run</option>
                                @foreach ($payrollRuns as $payrollRun)
                                    <option value="{{ $payrollRun->id }}" @selected((string) old('payroll_run_id') === (string) $payrollRun->id)>
                                        {{ $payrollRun->title ?? 'Payroll Run #'.$payrollRun->id }}
                                        @if (isset($payrollRun->period_month, $payrollRun->period_year))
                                            ({{ $payrollRun->period_month }}/{{ $payrollRun->period_year }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('payroll_run_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($monthColumn)
                        <div class="col-12 col-lg-6">
                            <label for="{{ $monthColumn }}" class="form-label">{{ $monthColumn === 'payroll_month' ? 'Payroll Month' : 'Month' }} <span class="text-danger">*</span></label>
                            <input
                                id="{{ $monthColumn }}"
                                type="{{ $monthColumn === 'payroll_month' ? 'month' : 'number' }}"
                                name="{{ $monthColumn }}"
                                value="{{ old($monthColumn) }}"
                                class="form-control @error($monthColumn) is-invalid @enderror"
                                @if ($monthColumn !== 'payroll_month') min="1" max="12" @endif
                                required
                            >
                            @error($monthColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($yearColumn)
                        <div class="col-12 col-lg-6">
                            <label for="{{ $yearColumn }}" class="form-label">Year <span class="text-danger">*</span></label>
                            <input
                                id="{{ $yearColumn }}"
                                type="number"
                                name="{{ $yearColumn }}"
                                value="{{ old($yearColumn) }}"
                                class="form-control @error($yearColumn) is-invalid @enderror"
                                min="2000"
                                max="2100"
                                required
                            >
                            @error($yearColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($salaryColumn)
                        <div class="col-12 col-lg-4">
                            <label for="{{ $salaryColumn }}" class="form-label">{{ $salaryColumn === 'gross_salary' ? 'Gross Salary' : ($salaryColumn === 'basic_salary' ? 'Basic Salary' : 'Salary') }} <span class="text-danger">*</span></label>
                            <input
                                id="{{ $salaryColumn }}"
                                type="number"
                                step="0.01"
                                min="0"
                                name="{{ $salaryColumn }}"
                                value="{{ old($salaryColumn) }}"
                                class="form-control @error($salaryColumn) is-invalid @enderror"
                                required
                            >
                            @error($salaryColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($allowanceColumn)
                        <div class="col-12 col-lg-4">
                            <label for="{{ $allowanceColumn }}" class="form-label">Allowances</label>
                            <input
                                id="{{ $allowanceColumn }}"
                                type="number"
                                step="0.01"
                                min="0"
                                name="{{ $allowanceColumn }}"
                                value="{{ old($allowanceColumn) }}"
                                class="form-control @error($allowanceColumn) is-invalid @enderror"
                            >
                            @error($allowanceColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($deductionColumn)
                        <div class="col-12 col-lg-4">
                            <label for="{{ $deductionColumn }}" class="form-label">Deductions</label>
                            <input
                                id="{{ $deductionColumn }}"
                                type="number"
                                step="0.01"
                                min="0"
                                name="{{ $deductionColumn }}"
                                value="{{ old($deductionColumn) }}"
                                class="form-control @error($deductionColumn) is-invalid @enderror"
                            >
                            @error($deductionColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-4">
                    @if ($hasNetSalary)
                        <div class="col-12 col-lg-4">
                            <label for="net_salary" class="form-label">Net Salary</label>
                            <input
                                id="net_salary"
                                type="number"
                                step="0.01"
                                name="net_salary"
                                value="{{ old('net_salary') }}"
                                class="form-control @error('net_salary') is-invalid @enderror"
                                readonly
                            >
                            <div class="form-text">Net salary is calculated by the service when supported by schema.</div>
                            @error('net_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($statusColumn)
                        <div class="col-12 col-lg-4">
                            <label for="{{ $statusColumn }}" class="form-label">{{ $statusColumn === 'payment_status' ? 'Payment Status' : 'Status' }}</label>
                            <select id="{{ $statusColumn }}" name="{{ $statusColumn }}" class="form-select @error($statusColumn) is-invalid @enderror">
                                <option value="draft" @selected(old($statusColumn, 'draft') === 'draft')>Draft</option>
                                <option value="pending" @selected(old($statusColumn) === 'pending')>Pending</option>
                                <option value="paid" @selected(old($statusColumn) === 'paid')>Paid</option>
                                <option value="approved" @selected(old($statusColumn) === 'approved')>Approved</option>
                                <option value="cancelled" @selected(old($statusColumn) === 'cancelled')>Cancelled</option>
                            </select>
                            @error($statusColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($paymentDateColumn)
                        <div class="col-12 col-lg-4">
                            <label for="{{ $paymentDateColumn }}" class="form-label">{{ $paymentDateColumn === 'paid_at' ? 'Paid At' : 'Payment Date' }}</label>
                            <input
                                id="{{ $paymentDateColumn }}"
                                type="{{ $paymentDateColumn === 'paid_at' ? 'datetime-local' : 'date' }}"
                                name="{{ $paymentDateColumn }}"
                                value="{{ old($paymentDateColumn) }}"
                                class="form-control @error($paymentDateColumn) is-invalid @enderror"
                            >
                            @error($paymentDateColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                @if ($noteColumn)
                    <div class="mb-4">
                        <label for="{{ $noteColumn }}" class="form-label">{{ $noteColumn === 'remarks' ? 'Remarks' : 'Note' }}</label>
                        <textarea
                            id="{{ $noteColumn }}"
                            name="{{ $noteColumn }}"
                            rows="4"
                            class="form-control @error($noteColumn) is-invalid @enderror"
                        >{{ old($noteColumn) }}</textarea>
                        @error($noteColumn)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.payrolls.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Payroll</button>
                </div>
            </form>
        </div>
    </div>
@endsection
