<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Designation\StoreDesignationRequest;
use App\Http\Requests\Admin\Designation\UpdateDesignationRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Services\DesignationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function index(): View
    {
        $countableRelationships = array_filter(
            ['employees'],
            fn (string $relationship): bool => method_exists(Designation::class, $relationship),
        );

        $designations = Designation::query()
            ->with('department')
            ->when($countableRelationships !== [], fn ($query) => $query->withCount($countableRelationships))
            ->latest()
            ->paginate(15);

        return view('admin.designations.index', compact('designations'));
    }

    public function create(): View
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get();

        return view('admin.designations.create', compact('departments'));
    }

    public function store(StoreDesignationRequest $request, DesignationService $designationService): RedirectResponse
    {
        $designationService->create($request->validated());

        return redirect()
            ->route('admin.designations.index')
            ->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation): View
    {
        $designation->load('department');

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        return view('admin.designations.edit', compact('designation', 'departments'));
    }

    public function update(
        UpdateDesignationRequest $request,
        Designation $designation,
        DesignationService $designationService
    ): RedirectResponse {
        $designationService->update($designation, $request->validated());

        return redirect()
            ->route('admin.designations.edit', $designation)
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation, DesignationService $designationService): RedirectResponse
    {
        try {
            $designationService->delete($designation);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.designations.index')
            ->with('success', 'Designation deleted successfully.');
    }
}
