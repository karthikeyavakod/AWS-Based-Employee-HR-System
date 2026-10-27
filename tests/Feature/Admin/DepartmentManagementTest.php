<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_departments_index_is_redirected_to_login(): void
    {
        $this->get('/admin/departments')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_department_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.departments.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_departments(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('People Operations'));

        $this->actingAs($admin)
            ->get(route('admin.departments.index'))
            ->assertOk()
            ->assertSeeText('Departments')
            ->assertSeeText($department->name);
    }

    public function test_super_admin_can_create_a_department(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->departmentPayload('Finance');

        $this->actingAs($admin)
            ->post(route('admin.departments.store'), $payload)
            ->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', $this->departmentDatabaseAssertion($payload));
    }

    public function test_super_admin_can_update_a_department(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Original Department'));
        $payload = $this->departmentPayload('Updated Department');

        $this->actingAs($admin)
            ->put(route('admin.departments.update', $department), $payload)
            ->assertRedirect(route('admin.departments.edit', $department))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', array_merge(
            ['id' => $department->id],
            $this->departmentDatabaseAssertion($payload),
        ));
    }

    public function test_super_admin_can_delete_an_unused_department(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Unused Department'));

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department))
            ->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('success');

        if (Schema::hasColumn('departments', 'deleted_at')) {
            $this->assertSoftDeleted('departments', [
                'id' => $department->id,
            ]);

            return;
        }

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
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

    /**
     * @return array<string, string>
     */
    private function departmentPayload(string $name): array
    {
        return $this->departmentAttributes($name);
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
     * @param array<string, string> $payload
     *
     * @return array<string, string>
     */
    private function departmentDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only(['name', 'code', 'slug', 'description', 'status'])
            ->all();
    }
}
