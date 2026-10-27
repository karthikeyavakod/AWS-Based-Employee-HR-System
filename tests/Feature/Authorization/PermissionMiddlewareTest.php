<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_dashboard_is_redirected_to_login(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_dashboard_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertForbidden();
    }

    public function test_super_admin_with_dashboard_view_permission_can_access_dashboard(): void
    {
        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
            SuperAdminSeeder::class,
        ]);

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Super Admin.');
    }
}
