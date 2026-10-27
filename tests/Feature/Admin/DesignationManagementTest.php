<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DesignationManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_designations_index_is_redirected_to_login(): void
    {
        $this->get('/admin/designations')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_designation_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.designations.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_designations(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('People Operations'));
        $designation = Designation::create($this->designationAttributes('HR Specialist', $department));

        $this->actingAs($admin)
            ->get(route('admin.designations.index'))
            ->assertOk()
            ->assertSeeText('Designations')
            ->assertSeeText($designation->name);
    }

    public function test_super_admin_can_create_a_designation(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Finance'));
        $payload = $this->designationPayload('Finance Manager', $department);

        $this->actingAs($admin)
            ->post(route('admin.designations.store'), $payload)
            ->assertRedirect(route('admin.designations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('designations', $this->designationDatabaseAssertion($payload));
    }

    public function test_super_admin_can_update_a_designation(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Original Department'));
        $newDepartment = Department::create($this->departmentAttributes('Updated Department'));
        $designation = Designation::create($this->designationAttributes('Original Designation', $department));
        $payload = $this->designationPayload('Updated Designation', $newDepartment);

        $this->actingAs($admin)
            ->put(route('admin.designations.update', $designation), $payload)
            ->assertRedirect(route('admin.designations.edit', $designation))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('designations', array_merge(
            ['id' => $designation->id],
            $this->designationDatabaseAssertion($payload),
        ));
    }

    public function test_super_admin_can_delete_an_unused_designation(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Operations'));
        $designation = Designation::create($this->designationAttributes('Unused Designation', $department));

        $this->actingAs($admin)
            ->delete(route('admin.designations.destroy', $designation))
            ->assertRedirect(route('admin.designations.index'))
            ->assertSessionHas('success');

        if (Schema::hasColumn('designations', 'deleted_at')) {
            $this->assertSoftDeleted('designations', [
                'id' => $designation->id,
            ]);

            return;
        }

        $this->assertDatabaseMissing('designations', [
            'id' => $designation->id,
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
    private function designationPayload(string $name, Department $department): array
    {
        return $this->designationAttributes($name, $department);
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
     * @param array<string, string|int> $payload
     *
     * @return array<string, string|int>
     */
    private function designationDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only(['department_id', 'name', 'code', 'slug', 'description', 'status'])
            ->all();
    }
}
