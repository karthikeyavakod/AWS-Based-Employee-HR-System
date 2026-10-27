<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_employees_index_is_redirected_to_login(): void
    {
        $this->get('/admin/employees')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_employee_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.employees.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_employees(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('People Operations'));
        $designation = Designation::create($this->designationAttributes('HR Specialist', $department));
        $employee = $this->createEmployee('Ava', 'Stone', $department, $designation);
        $employeeName = Schema::hasColumn('employees', 'user_id')
            ? $employee->user?->name
            : trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        $this->actingAs($admin)
            ->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSeeText('Employees')
            ->assertSeeText($employeeName ?: 'N/A');
    }

    public function test_super_admin_can_create_an_employee(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Finance'));
        $designation = Designation::create($this->designationAttributes('Finance Manager', $department));
        $payload = $this->employeePayload('Maya', 'Reed', $department, $designation);

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $payload)
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('employees', $this->employeeDatabaseAssertion($payload));
    }

    public function test_creating_employee_also_creates_related_user_account_when_supported(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Operations'));
        $designation = Designation::create($this->designationAttributes('Operations Associate', $department));
        $payload = $this->employeePayload('Noah', 'Blake', $department, $designation);

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $payload)
            ->assertRedirect(route('admin.employees.index'));

        if (! Schema::hasColumn('employees', 'user_id')) {
            $this->assertDatabaseHas('employees', $this->employeeDatabaseAssertion($payload));

            return;
        }

        $user = User::where('email', $payload['email'])->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        $this->assertDatabaseHas('employees', array_merge(
            $this->employeeDatabaseAssertion($payload),
            ['user_id' => $user->id],
        ));
    }

    public function test_created_employee_user_receives_employee_role_when_role_exists(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Support'));
        $designation = Designation::create($this->designationAttributes('Support Specialist', $department));
        $payload = $this->employeePayload('Lina', 'Hart', $department, $designation);

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $payload)
            ->assertRedirect(route('admin.employees.index'));

        if (! Schema::hasColumn('employees', 'user_id') || ! Schema::hasTable('role_user')) {
            $this->assertDatabaseHas('employees', $this->employeeDatabaseAssertion($payload));

            return;
        }

        $role = Role::where('slug', 'employee')
            ->orWhere('name', 'Employee')
            ->first();

        if ($role === null) {
            $this->assertDatabaseHas('employees', $this->employeeDatabaseAssertion($payload));

            return;
        }

        $user = User::where('email', $payload['email'])->firstOrFail();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_super_admin_can_update_an_employee(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Original Department'));
        $designation = Designation::create($this->designationAttributes('Original Designation', $department));
        $newDepartment = Department::create($this->departmentAttributes('Updated Department'));
        $newDesignation = Designation::create($this->designationAttributes('Updated Designation', $newDepartment));
        $employee = $this->createEmployee('Original', 'Employee', $department, $designation);
        $payload = $this->employeePayload('Updated', 'Employee', $newDepartment, $newDesignation);

        $this->actingAs($admin)
            ->put(route('admin.employees.update', $employee), $payload)
            ->assertRedirect(route('admin.employees.edit', $employee))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('employees', array_merge(
            ['id' => $employee->id],
            $this->employeeDatabaseAssertion($payload),
        ));

        if (Schema::hasColumn('employees', 'user_id')) {
            $this->assertDatabaseHas('users', [
                'id' => $employee->user_id,
                'name' => $payload['name'],
                'email' => $payload['email'],
            ]);
        }
    }

    public function test_super_admin_can_delete_an_employee_safely(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Temporary Department'));
        $designation = Designation::create($this->designationAttributes('Temporary Designation', $department));
        $employee = $this->createEmployee('Temporary', 'Employee', $department, $designation);
        $userId = $employee->user_id;

        $this->actingAs($admin)
            ->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success');

        if (Schema::hasColumn('employees', 'deleted_at')) {
            $this->assertSoftDeleted('employees', [
                'id' => $employee->id,
            ]);
        } else {
            $this->assertDatabaseMissing('employees', [
                'id' => $employee->id,
            ]);
        }

        if ($userId !== null && Schema::hasColumn('users', 'status')) {
            $this->assertDatabaseHas('users', [
                'id' => $userId,
                'status' => 'inactive',
            ]);
        }
    }

    private function superAdmin(): User
    {
        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
            SuperAdminSeeder::class,
        ]);

        return User::where('email', 'admin@example.com')->firstOrFail();
    }

    private function createEmployee(
        string $firstName,
        string $lastName,
        Department $department,
        Designation $designation
    ): Employee {
        $payload = $this->employeePayload($firstName, $lastName, $department, $designation);
        $attributes = $this->employeeDatabaseAssertion($payload);

        if (Schema::hasColumn('employees', 'user_id')) {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => bcrypt('Password@12345'),
                'status' => 'active',
            ]);

            $attributes['user_id'] = $user->id;
        }

        return Employee::create($attributes);
    }

    /**
     * @return array<string, string>
     */
    private function departmentAttributes(string $name): array
    {
        $suffix = Str::lower(Str::random(8));
        $fullName = "{$name} {$suffix}";
        $attributes = [
            'name' => $fullName,
        ];

        if (Schema::hasColumn('departments', 'code')) {
            $attributes['code'] = Str::upper(Str::slug($fullName, '-'));
        }

        if (Schema::hasColumn('departments', 'slug')) {
            $attributes['slug'] = Str::slug($fullName);
        }

        if (Schema::hasColumn('departments', 'description')) {
            $attributes['description'] = "Test department for {$name}.";
        }

        if (Schema::hasColumn('departments', 'status')) {
            $attributes['status'] = 'active';
        }

        return $attributes;
    }

    /**
     * @return array<string, string|int>
     */
    private function designationAttributes(string $name, Department $department): array
    {
        $suffix = Str::lower(Str::random(8));
        $fullName = "{$name} {$suffix}";
        $attributes = [
            'name' => $fullName,
        ];

        if (Schema::hasColumn('designations', 'department_id')) {
            $attributes['department_id'] = $department->id;
        }

        if (Schema::hasColumn('designations', 'code')) {
            $attributes['code'] = Str::upper(Str::slug($fullName, '-'));
        }

        if (Schema::hasColumn('designations', 'slug')) {
            $attributes['slug'] = Str::slug($fullName);
        }

        if (Schema::hasColumn('designations', 'description')) {
            $attributes['description'] = "Test designation for {$name}.";
        }

        if (Schema::hasColumn('designations', 'status')) {
            $attributes['status'] = 'active';
        }

        return $attributes;
    }

    /**
     * @return array<string, string|int>
     */
    private function employeePayload(string $firstName, string $lastName, Department $department, Designation $designation): array
    {
        $suffix = Str::lower(Str::random(8));
        $fullName = "{$firstName} {$lastName} {$suffix}";
        $payload = [
            'name' => $fullName,
            'email' => "employee-{$suffix}@example.com",
        ];

        if (Schema::hasColumn('employees', 'department_id')) {
            $payload['department_id'] = $department->id;
        }

        if (Schema::hasColumn('employees', 'designation_id')) {
            $payload['designation_id'] = $designation->id;
        }

        if (Schema::hasColumn('employees', 'employee_code')) {
            $payload['employee_code'] = Str::upper(Str::slug("{$firstName} {$lastName} {$suffix}", '-'));
        }

        if (Schema::hasColumn('employees', 'code')) {
            $payload['code'] = Str::upper(Str::slug("{$firstName} {$lastName} {$suffix}", '-'));
        }

        if (Schema::hasColumn('employees', 'slug')) {
            $payload['slug'] = Str::slug("{$firstName} {$lastName} {$suffix}");
        }

        if (Schema::hasColumn('employees', 'first_name')) {
            $payload['first_name'] = "{$firstName} {$suffix}";
        }

        if (Schema::hasColumn('employees', 'last_name')) {
            $payload['last_name'] = $lastName;
        }

        if (Schema::hasColumn('employees', 'phone')) {
            $payload['phone'] = '555-'.Str::upper(Str::random(6));
        }

        if (Schema::hasColumn('employees', 'gender')) {
            $payload['gender'] = 'other';
        }

        if (Schema::hasColumn('employees', 'date_of_birth')) {
            $payload['date_of_birth'] = '1990-01-15';
        }

        if (Schema::hasColumn('employees', 'joining_date')) {
            $payload['joining_date'] = '2026-01-15';
        }

        if (Schema::hasColumn('employees', 'address')) {
            $payload['address'] = 'Test employee address';
        }

        if (Schema::hasColumn('employees', 'employment_type')) {
            $payload['employment_type'] = 'full_time';
        }

        if (Schema::hasColumn('employees', 'status')) {
            $payload['status'] = 'active';
        }

        return $payload;
    }

    /**
     * @param array<string, string|int> $payload
     *
     * @return array<string, string|int>
     */
    private function employeeDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only([
                'department_id',
                'designation_id',
                'employee_code',
                'code',
                'slug',
                'first_name',
                'last_name',
                'phone',
                'gender',
                'date_of_birth',
                'joining_date',
                'address',
                'employment_type',
                'status',
            ])
            ->all();
    }
}
