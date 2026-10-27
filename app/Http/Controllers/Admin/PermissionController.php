<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $permissionGroups = $permissions->groupBy(
            fn (Permission $permission) => $permission->module ?: str($permission->slug)->before('.')->toString()
        );

        return view('admin.permissions.index', compact('permissionGroups'));
    }
}
