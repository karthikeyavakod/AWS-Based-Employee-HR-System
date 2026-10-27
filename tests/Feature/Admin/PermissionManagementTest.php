<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_permissions_index_is_redirected_to_login(): void
    {
        $this->get('/admin/permissions')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_permission_view_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.permissions.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_permissions(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.permissions.index'))
            ->assertOk()
            ->assertSeeText('Permissions')
            ->assertSeeText('Dashboard View')
            ->assertDontSeeText('dashboard.view');
    }

    public function test_permissions_page_is_read_only(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.permissions.index'))
            ->assertOk()
            ->assertDontSeeText('Create Permission')
            ->assertDontSeeText('Edit Permission')
            ->assertDontSeeText('Delete Permission')
            ->assertDontSee('/admin/permissions/create')
            ->assertDontSee('method="POST" action="http://localhost/admin/permissions"');
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
}
