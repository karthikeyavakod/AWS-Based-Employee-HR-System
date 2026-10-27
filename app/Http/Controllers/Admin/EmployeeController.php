<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Employee\StoreEmployeeRequest;
use App\Http\Requests\Admin\Employee\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\EmployeeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
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

        return view('admin.employees.create', compact('departments', 'designations'));
    }

    public function store(StoreEmployeeRequest $request, EmployeeService $employeeService): RedirectResponse
    {
        $employeeService->create($request->validated());

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'department', 'designation']);

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        $designations = Designation::query()
            ->with('department')
            ->orderBy('name')
            ->get();

        return view('admin.employees.edit', compact('employee', 'departments', 'designations'));
    }

    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee,
        EmployeeService $employeeService
    ): RedirectResponse {
        $employeeService->update($employee, $request->validated());

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
}
