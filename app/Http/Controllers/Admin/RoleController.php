<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\SyncRolePermissionsRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->latest()
            ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create');
    }

    public function store(StoreRoleRequest $request, RoleService $roleService): RedirectResponse
    {
        $roleService->create($request->validated());

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(UpdateRoleRequest $request, Role $role, RoleService $roleService): RedirectResponse
    {
        $roleService->update($role, $request->validated());

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role, RoleService $roleService): RedirectResponse
    {
        try {
            $roleService->delete($role);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function editPermissions(Role $role): View
    {
        $role->load('permissions:id');
        $assignedPermissionIds = $role->permissions->pluck('id')->all();

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->module ?: str($permission->slug)->before('.')->toString());

        return view('admin.roles.permissions', compact('role', 'permissions', 'assignedPermissionIds'));
    }

    public function updatePermissions(
        SyncRolePermissionsRequest $request,
        Role $role,
        RoleService $roleService
    ): RedirectResponse {
        $roleService->syncPermissions($role, $request->validated('permissions', []));

        return redirect()
            ->route('admin.roles.permissions.edit', $role)
            ->with('success', 'Role permissions updated successfully.');
    }
}
