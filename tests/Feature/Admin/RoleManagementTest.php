<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_roles_index_is_redirected_to_login(): void
    {
        $this->get('/admin/roles')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_role_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_roles(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSeeText('Roles')
            ->assertSeeText('Super Admin');
    }

    public function test_super_admin_can_create_a_role(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->rolePayload('Operations Lead');

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), $payload)
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', $this->roleDatabaseAssertion($payload));
    }

    public function test_super_admin_can_update_a_role(): void
    {
        $admin = $this->superAdmin();
        $role = Role::create($this->roleAttributes('Original Role'));
        $payload = $this->rolePayload('Updated Role');

        $this->actingAs($admin)
            ->put(route('admin.roles.update', $role), $payload)
            ->assertRedirect(route('admin.roles.edit', $role))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', array_merge(
            ['id' => $role->id],
            $this->roleDatabaseAssertion($payload),
        ));
    }

    public function test_super_admin_cannot_delete_the_super_admin_role(): void
    {
        $admin = $this->superAdmin();
        $role = Role::where('slug', 'super-admin')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.roles.index'))
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_super_admin_cannot_delete_a_role_that_is_assigned_to_a_user(): void
    {
        $admin = $this->superAdmin();
        $role = Role::create($this->roleAttributes('Assigned Role'));
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->roles()->attach($role->id);

        $this->actingAs($admin)
            ->from(route('admin.roles.index'))
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_super_admin_can_sync_permissions_for_a_role(): void
    {
        $admin = $this->superAdmin();
        $role = Role::create($this->roleAttributes('Permission Sync Role'));
        $permissionIds = Permission::whereIn('slug', ['dashboard.view', 'employee.view'])
            ->pluck('id')
            ->all();

        $this->actingAs($admin)
            ->put(route('admin.roles.permissions.update', $role), [
                'permissions' => $permissionIds,
            ])
            ->assertRedirect(route('admin.roles.permissions.edit', $role))
            ->assertSessionHas('success');

        $this->assertEqualsCanonicalizing(
            $permissionIds,
            $role->fresh()->permissions()->pluck('permissions.id')->all(),
        );
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
    private function rolePayload(string $name): array
    {
        return $this->roleAttributes($name);
    }

    /**
     * @return array<string, string>
     */
    private function roleAttributes(string $name): array
    {
        $suffix = Str::lower(Str::random(8));
        $attributes = [
            'name' => "{$name} {$suffix}",
        ];

        if (Schema::hasColumn('roles', 'slug')) {
            $attributes['slug'] = Str::slug("{$name} {$suffix}");
        }

        if (Schema::hasColumn('roles', 'description')) {
            $attributes['description'] = "Test role for {$name}.";
        }

        if (Schema::hasColumn('roles', 'status')) {
            $attributes['status'] = 'active';
        }

        return $attributes;
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array<string, string>
     */
    private function roleDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only(['name', 'slug', 'description', 'status'])
            ->all();
    }
}
