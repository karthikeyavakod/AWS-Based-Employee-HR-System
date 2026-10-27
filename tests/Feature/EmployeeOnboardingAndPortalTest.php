<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeOnboardingAndPortalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_onboarding_creates_inactive_account_and_sends_password_setup_link(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $department = $this->department();
        $designation = $this->designation($department);
        $role = Role::where('slug', 'hr-manager')->firstOrFail();
        $payload = $this->employeePayload($department, $designation) + ['role_id' => $role->id];

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), $payload)
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success');

        $user = User::where('email', $payload['email'])->firstOrFail();

        $this->assertSame('inactive', $user->status);
        $this->assertFalse(Hash::check('Password@12345', $user->password));
        $this->assertTrue($user->hasRole('hr-manager'));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_onboarding_cannot_assign_the_super_admin_role(): void
    {
        $admin = $this->superAdmin();
        $department = $this->department();
        $designation = $this->designation($department);
        $payload = $this->employeePayload($department, $designation) + [
            'role_id' => Role::where('slug', 'super-admin')->value('id'),
        ];

        $this->actingAs($admin)
            ->from(route('admin.employees.create'))
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
    }

    public function test_hr_manager_cannot_assign_roles_during_employee_onboarding(): void
    {
        $this->superAdmin();
        $manager = User::factory()->create(['status' => 'active']);
        $manager->roles()->sync([Role::where('slug', 'hr-manager')->value('id')]);
        $department = $this->department();
        $designation = $this->designation($department);
        $payload = $this->employeePayload($department, $designation) + [
            'role_id' => Role::where('slug', 'hr-manager')->value('id'),
        ];

        $this->actingAs($manager)
            ->from(route('admin.employees.create'))
            ->post(route('admin.employees.store'), $payload)
            ->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
    }

    public function test_password_setup_activates_account_and_allows_login(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'A-secure-employee-password',
            'password_confirmation' => 'A-secure-employee-password',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('A-secure-employee-password', $user->password));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'A-secure-employee-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_accounts_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('A-secure-employee-password'),
            'status' => 'inactive',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'A-secure-employee-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_employee_can_check_in_once_and_check_out_with_calculated_hours(): void
    {
        $this->superAdmin();
        $employee = $this->employeeUser('Attendance Employee');
        $this->travelTo(Carbon::parse('2026-10-28 09:00:00'));

        $this->actingAs($employee->user)
            ->post(route('employee.attendance.check-in'))
            ->assertRedirect(route('employee.dashboard'))
            ->assertSessionHas('success', 'You are checked in.');

        $this->actingAs($employee->user)
            ->from(route('employee.dashboard'))
            ->post(route('employee.attendance.check-in'))
            ->assertSessionHasErrors('attendance_date');

        $this->travelTo(Carbon::parse('2026-10-28 18:00:00'));
        $this->actingAs($employee->user)
            ->post(route('employee.attendance.check-out'))
            ->assertRedirect(route('employee.dashboard'))
            ->assertSessionHas('success', 'You are checked out.');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-28',
            'check_in_at' => '2026-10-28 09:00:00',
            'check_out_at' => '2026-10-28 18:00:00',
            'work_minutes' => 540,
        ]);
    }

    public function test_employee_portal_shows_only_own_record_and_allows_contact_updates(): void
    {
        $this->superAdmin();
        $employee = $this->employeeUser('Portal Employee');
        $otherEmployee = $this->employeeUser('Private Colleague');

        $this->actingAs($employee->user)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSeeText('Portal Employee')
            ->assertDontSeeText('Private Colleague')
            ->assertDontSeeText($otherEmployee->user->email);

        $this->actingAs($employee->user)
            ->put(route('employee.profile.update'), [
                'phone' => '555-0101',
                'address' => 'New home address',
            ])
            ->assertRedirect(route('employee.profile.edit'))
            ->assertSessionHas('success');

        $this->actingAs($employee->user)
            ->put(route('employee.profile.update'), [
                'name' => 'Changed Account Name',
                'status' => 'inactive',
                'department_id' => $otherEmployee->department_id,
                'role_id' => Role::where('slug', 'hr-manager')->value('id'),
            ])
            ->assertSessionHasErrors(['name', 'status', 'department_id', 'role_id']);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'phone' => '555-0101',
            'address' => 'New home address',
            'department_id' => $employee->department_id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'name' => 'Portal Employee',
            'status' => 'active',
        ]);
    }

    public function test_non_employee_cannot_access_employee_portal(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->get(route('employee.dashboard'))
            ->assertForbidden();
    }

    public function test_employee_leave_request_is_limited_to_their_own_pending_request(): void
    {
        $this->superAdmin();
        $employee = $this->employeeUser('Leave Requester');
        $otherEmployee = $this->employeeUser('Other Employee');
        $leaveTypeId = DB::table('leave_types')->insertGetId([
            'name' => 'Annual Leave',
            'code' => 'ANNUAL-'.Str::upper(Str::random(8)),
            'default_days' => 10,
            'is_paid' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(6)->toDateString();

        $this->actingAs($employee->user)
            ->post(route('admin.leaves.store'), [
                'employee_id' => $otherEmployee->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'approved',
                'total_days' => 90,
            ])
            ->assertSessionHasErrors(['employee_id', 'status', 'total_days']);

        $this->assertDatabaseCount('leave_requests', 0);

        $this->actingAs($employee->user)
            ->post(route('admin.leaves.store'), [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => 'Personal leave',
            ])
            ->assertRedirect(route('employee.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveTypeId,
            'status' => 'pending',
            'total_days' => 2,
        ]);

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)->firstOrFail();
        $employeeRole = Role::where('slug', 'employee')->firstOrFail();
        $approvePermission = Permission::where('slug', 'leave.approve')->firstOrFail();
        $employeeRole->permissions()->syncWithoutDetaching([$approvePermission->id]);

        $this->actingAs($employee->user)
            ->patch(route('admin.leaves.approve', $leaveRequest))
            ->assertRedirect()
            ->assertSessionHas('error', 'Employees cannot approve their own leave requests.');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'pending',
        ]);
    }

    private function superAdmin(): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, SuperAdminSeeder::class]);

        return User::where('email', 'admin@example.com')->firstOrFail();
    }

    private function department(): Department
    {
        $suffix = Str::lower(Str::random(8));

        return Department::create([
            'name' => 'Department '.$suffix,
            'code' => Str::upper($suffix),
            'status' => 'active',
        ]);
    }

    private function designation(Department $department): Designation
    {
        $suffix = Str::lower(Str::random(8));

        return Designation::create([
            'department_id' => $department->id,
            'name' => 'Designation '.$suffix,
            'code' => Str::upper($suffix),
            'status' => 'active',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function employeePayload(Department $department, Designation $designation): array
    {
        $suffix = Str::lower(Str::random(10));

        return [
            'name' => 'New Employee '.$suffix,
            'email' => 'employee-'.$suffix.'@example.test',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'employee_code' => 'EMP-'.Str::upper($suffix),
            'first_name' => 'New',
            'last_name' => 'Employee',
            'joining_date' => now()->toDateString(),
            'employment_type' => 'full_time',
            'status' => 'active',
        ];
    }

    private function employeeUser(string $name): Employee
    {
        $department = $this->department();
        $designation = $this->designation($department);
        $user = User::factory()->create(['name' => $name, 'status' => 'active']);
        $role = Role::where('slug', 'employee')->firstOrFail();
        $user->roles()->sync([$role->id]);

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'employee_code' => 'EMP-'.Str::upper(Str::random(10)),
            'first_name' => $name,
            'last_name' => 'Test',
            'joining_date' => now()->toDateString(),
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);
    }
}
