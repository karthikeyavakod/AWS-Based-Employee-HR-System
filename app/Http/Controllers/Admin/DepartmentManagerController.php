<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentManager;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DepartmentManagerController extends Controller
{
    public function index(): View
    {
        $departmentManagers = DepartmentManager::query()
            ->with(['department', 'user'])
            ->latest()
            ->paginate(15);

        return view('admin.department-managers.index', compact('departmentManagers'));
    }

    public function create(): View
    {
        $departments = Department::query()->where('status', 'active')->orderBy('name')->get();
        $managers = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('slug', ['hr-manager', 'department-manager']);
            })
            ->orderBy('name')
            ->get();

        return view('admin.department-managers.create', compact('departments', 'managers'));
    }

    public function store(\Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:department_managers,department_id,NULL,NULL,user_id,' . $request->user_id],
        ]);

        DB::transaction(function () use ($request) {
            DepartmentManager::create([
                'department_id' => $request->department_id,
                'user_id' => $request->user_id,
            ]);

            $user = User::find($request->user_id);
            $role = Role::where('slug', 'department-manager')->first();
            if ($role && !$user->hasRole('department-manager')) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        });

        return redirect()->route('admin.department-managers.index')->with('success', 'Department manager assigned successfully.');
    }

    public function destroy(DepartmentManager $departmentManager): RedirectResponse
    {
        $departmentManager->delete();

        return redirect()->route('admin.department-managers.index')->with('success', 'Department manager removed successfully.');
    }
}