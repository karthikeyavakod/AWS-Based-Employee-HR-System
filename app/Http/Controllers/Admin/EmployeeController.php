<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Employee\StoreEmployeeRequest;
use App\Http\Requests\Admin\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Role;
use App\Services\EmployeeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $relationships = array_filter(
            ['user', 'department', 'designation'],
            fn (string $relationship): bool => method_exists(Employee::class, $relationship),
        );

        $employees = Employee::query()
            ->with($relationships)
            ->latest()
            ->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get();

        $designations = Designation::query()
            ->with('department')
            ->orderBy('name')
            ->get();
        $roles = auth()->user()?->hasPermission('user.assign-role')
            ? Role::query()->where('slug', '!=', 'super-admin')->orderBy('name')->get()
            : collect();

        return view('admin.employees.create', compact('departments', 'designations', 'roles'));
    }

    public function store(StoreEmployeeRequest $request, EmployeeService $employeeService): RedirectResponse
    {
        $employee = $employeeService->create($request->validated());

        if ($employee->user !== null) {
            $status = Password::sendResetLink(['email' => $employee->user->email]);
            $message = $status === Password::RESET_LINK_SENT
                ? 'Employee created. A secure account setup link was sent to their email.'
                : 'Employee created, but the account setup email could not be sent. The account remains inactive.';

            return redirect()
                ->route('admin.employees.index')
                ->with($status === Password::RESET_LINK_SENT ? 'success' : 'error', $message);
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user.roles', 'department', 'designation']);

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        $designations = Designation::query()
            ->with('department')
            ->orderBy('name')
            ->get();
        $roles = auth()->user()?->hasPermission('user.assign-role')
            ? Role::query()->where('slug', '!=', 'super-admin')->orderBy('name')->get()
            : collect();

        return view('admin.employees.edit', compact('employee', 'departments', 'designations', 'roles'));
    }

    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee,
        EmployeeService $employeeService
    ): RedirectResponse {
        $accountAlreadyExisted = $employee->user !== null;
        $employee = $employeeService->update($employee, $request->validated());

        if (! $accountAlreadyExisted && $employee->user !== null) {
            $status = Password::sendResetLink(['email' => $employee->user->email]);
            $message = $status === Password::RESET_LINK_SENT
                ? 'Employee updated. A secure account setup link was sent to their email.'
                : 'Employee updated, but the account setup email could not be sent. The account remains inactive.';

            return redirect()
                ->route('admin.employees.edit', $employee)
                ->with($status === Password::RESET_LINK_SENT ? 'success' : 'error', $message);
        }

        return redirect()
            ->route('admin.employees.edit', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee, EmployeeService $employeeService): RedirectResponse
    {
        try {
            $employeeService->delete($employee);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function invite(Employee $employee): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('user.create'), 403);

        if (! Schema::hasColumn('employees', 'user_id') || $employee->user === null) {
            return back()->with('error', 'This employee does not have a linked account.');
        }

        if ($employee->user->status === 'active') {
            return back()->with('error', 'This account is already active. Use password reset instead.');
        }

        $status = Password::sendResetLink(['email' => $employee->user->email]);

        return back()
            ->with(
                $status === Password::RESET_LINK_SENT ? 'success' : 'error',
                $status === Password::RESET_LINK_SENT
                    ? 'A secure account setup link was sent to the employee.'
                    : 'The account setup link could not be sent. Please wait before retrying or check mail configuration.',
            );
    }
}
