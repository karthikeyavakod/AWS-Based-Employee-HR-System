<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $permissions = [
        'dashboard.view',
        'role.view',
        'role.create',
        'role.update',
        'role.delete',
        'permission.view',
        'permission.assign',
        'user.view',
        'user.create',
        'user.update',
        'user.delete',
        'employee.view',
        'employee.create',
        'employee.update',
        'employee.delete',
        'department.view',
        'department.create',
        'department.update',
        'department.delete',
        'designation.view',
        'designation.create',
        'designation.update',
        'designation.delete',
        'attendance.view',
        'attendance.create',
        'attendance.update',
        'attendance.delete',
        'leave.view',
        'leave.create',
        'leave.approve',
        'leave.reject',
        'leave.delete',
        'payroll.view',
        'payroll.create',
        'payroll.update',
        'payroll.delete',
        'report.view',
    ];

    /**
     * @var list<string>
     */
    private array $hrManagerPermissions = [
        'dashboard.view',
        'employee.view',
        'employee.create',
        'employee.update',
        'department.view',
        'department.create',
        'department.update',
        'designation.view',
        'designation.create',
        'designation.update',
        'attendance.view',
        'attendance.create',
        'attendance.update',
        'leave.view',
        'leave.approve',
        'leave.reject',
        'payroll.view',
        'report.view',
    ];

    /**
     * @var list<string>
     */
    private array $employeePermissions = [
        'dashboard.view',
        'leave.create',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $slug) {
            Permission::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $this->nameFromSlug($slug),
                    'module' => $this->moduleFromSlug($slug),
                    'description' => null,
                ],
            );
        }

        $this->assignPermissions('super-admin', Permission::query()->pluck('id')->all());
        $this->assignPermissions('hr-manager', $this->permissionIds($this->hrManagerPermissions));
        $this->assignPermissions('employee', $this->permissionIds($this->employeePermissions));
    }

    /**
     * @param list<int> $permissionIds
     */
    private function assignPermissions(string $roleSlug, array $permissionIds): void
    {
        Role::where('slug', $roleSlug)
            ->firstOrFail()
            ->permissions()
            ->sync($permissionIds);
    }

    /**
     * @param list<string> $slugs
     *
     * @return list<int>
     */
    private function permissionIds(array $slugs): array
    {
        return Permission::whereIn('slug', $slugs)
            ->pluck('id')
            ->all();
    }

    private function nameFromSlug(string $slug): string
    {
        return ucwords(str_replace(['.', '-'], ' ', $slug));
    }

    private function moduleFromSlug(string $slug): string
    {
        return explode('.', $slug, 2)[0];
    }
}
