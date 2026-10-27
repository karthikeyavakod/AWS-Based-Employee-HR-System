<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Department\StoreDepartmentRequest;
use App\Http\Requests\Admin\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $countableRelationships = array_filter(
            ['employees', 'designations'],
            fn (string $relationship): bool => method_exists(Department::class, $relationship),
        );

        $departments = Department::query()
            ->when($countableRelationships !== [], fn ($query) => $query->withCount($countableRelationships))
            ->latest()
            ->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(StoreDepartmentRequest $request, DepartmentService $departmentService): RedirectResponse
    {
        $departmentService->create($request->validated());

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(
        UpdateDepartmentRequest $request,
        Department $department,
        DepartmentService $departmentService
    ): RedirectResponse {
        $departmentService->update($department, $request->validated());

        return redirect()
            ->route('admin.departments.edit', $department)
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department, DepartmentService $departmentService): RedirectResponse
    {
        try {
            $departmentService->delete($department);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
