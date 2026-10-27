<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_leaves_index_is_redirected_to_login(): void
    {
        $this->get('/admin/leaves')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_leave_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.leaves.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_leave_requests(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Viewer');
        $leaveTypeId = $this->createLeaveType('Annual Leave');
        $leaveRequest = $this->createLeaveRequest($employee, $leaveTypeId, '2026-03-01', '2026-03-03');
        $employeeName = $employee->user?->name
            ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        $this->actingAs($admin)
            ->get(route('admin.leaves.index'))
            ->assertOk()
            ->assertSeeText('Leave Requests')
            ->assertSeeText($employeeName ?: 'N/A')
            ->assertSeeText($leaveRequest->start_date?->format('M d, Y') ?? 'Mar 01, 2026');
    }

    public function test_super_admin_can_create_a_leave_request(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Creator');
        $leaveTypeId = $this->createLeaveType('Sick Leave');
        $payload = $this->leavePayload($employee, $leaveTypeId, '2026-03-04', '2026-03-06');

        $this->actingAs($admin)
            ->post(route('admin.leaves.store'), $payload)
            ->assertRedirect(route('admin.leaves.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', $this->leaveDatabaseAssertion($payload));

        if (Schema::hasColumn('leave_requests', 'total_days')) {
            $this->assertDatabaseHas('leave_requests', [
                'employee_id' => $employee->id,
                'start_date' => '2026-03-04',
                'end_date' => '2026-03-06',
                'total_days' => 3,
            ]);
        }
    }

    public function test_super_admin_can_update_a_leave_request(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Updater');
        $leaveTypeId = $this->createLeaveType('Casual Leave');
        $leaveRequest = $this->createLeaveRequest($employee, $leaveTypeId, '2026-03-07', '2026-03-08');
        $payload = $this->leavePayload($employee, $leaveTypeId, '2026-03-09', '2026-03-11', 'pending');

        $this->actingAs($admin)
            ->put(route('admin.leaves.update', $leaveRequest), $payload)
            ->assertRedirect(route('admin.leaves.edit', $leaveRequest))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', array_merge(
            ['id' => $leaveRequest->id],
            $this->leaveDatabaseAssertion($payload),
        ));
    }

    public function test_super_admin_can_delete_a_leave_request(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Deleter');
        $leaveTypeId = $this->createLeaveType('Emergency Leave');
        $leaveRequest = $this->createLeaveRequest($employee, $leaveTypeId, '2026-03-12', '2026-03-13');

        $this->actingAs($admin)
            ->delete(route('admin.leaves.destroy', $leaveRequest))
            ->assertRedirect(route('admin.leaves.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('leave_requests', [
            'id' => $leaveRequest->id,
        ]);
    }

    public function test_super_admin_can_approve_a_leave_request(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Approver');
        $leaveTypeId = $this->createLeaveType('Approval Leave');
        $leaveRequest = $this->createLeaveRequest($employee, $leaveTypeId, '2026-03-14', '2026-03-15');

        $this->actingAs($admin)
            ->patch(route('admin.leaves.approve', $leaveRequest))
            ->assertRedirect()
            ->assertSessionHas('success');

        if (Schema::hasColumn('leave_requests', 'status')) {
            $this->assertDatabaseHas('leave_requests', [
                'id' => $leaveRequest->id,
                'status' => 'approved',
            ]);
        }

        if (Schema::hasColumn('leave_requests', 'approved_by')) {
            $this->assertDatabaseHas('leave_requests', [
                'id' => $leaveRequest->id,
                'approved_by' => $admin->id,
            ]);
        }

        if (Schema::hasColumn('leave_requests', 'approved_at')) {
            $this->assertNotNull($leaveRequest->refresh()->approved_at);
        }
    }

    public function test_super_admin_can_reject_a_leave_request(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Rejector');
        $leaveTypeId = $this->createLeaveType('Rejectable Leave');
        $leaveRequest = $this->createLeaveRequest($employee, $leaveTypeId, '2026-03-16', '2026-03-17');

        $this->actingAs($admin)
            ->patch(route('admin.leaves.reject', $leaveRequest))
            ->assertRedirect()
            ->assertSessionHas('success');

        if (Schema::hasColumn('leave_requests', 'status')) {
            $this->assertDatabaseHas('leave_requests', [
                'id' => $leaveRequest->id,
                'status' => 'rejected',
            ]);
        }
    }

    public function test_overlapping_leave_request_for_same_employee_and_date_range_is_prevented(): void
    {
        if (
            ! Schema::hasColumn('leave_requests', 'employee_id')
            || ! Schema::hasColumn('leave_requests', 'start_date')
            || ! Schema::hasColumn('leave_requests', 'end_date')
        ) {
            $this->markTestSkipped('Leave overlap prevention requires employee_id, start_date, and end_date columns.');
        }

        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Leave', 'Overlap');
        $leaveTypeId = $this->createLeaveType('Overlap Leave');
        $payload = $this->leavePayload($employee, $leaveTypeId, '2026-03-20', '2026-03-22');

        $this->createLeaveRequest($employee, $leaveTypeId, '2026-03-18', '2026-03-21');

        $this->actingAs($admin)
            ->from(route('admin.leaves.create'))
            ->post(route('admin.leaves.store'), $payload)
            ->assertRedirect(route('admin.leaves.create'))
            ->assertSessionHasErrors('start_date');

        $this->assertSame(
            1,
            LeaveRequest::where('employee_id', $employee->id)
                ->whereDate('start_date', '<=', '2026-03-22')
                ->whereDate('end_date', '>=', '2026-03-20')
                ->count(),
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

    private function createEmployee(string $firstName, string $lastName): Employee
    {
        $department = Department::create($this->departmentAttributes('Leave Department'));
        $designation = Designation::create($this->designationAttributes('Leave Designation', $department));
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

    private function createLeaveType(string $name): ?int
    {
        if (! Schema::hasTable('leave_types')) {
            return null;
        }

        $suffix = Str::lower(Str::random(8));
        $attributes = [];

        if (Schema::hasColumn('leave_types', 'name')) {
            $attributes['name'] = "{$name} {$suffix}";
        }

        if (Schema::hasColumn('leave_types', 'code')) {
            $attributes['code'] = Str::upper(Str::slug("{$name} {$suffix}", '-'));
        }

        if (Schema::hasColumn('leave_types', 'default_days')) {
            $attributes['default_days'] = 12;
        }

        if (Schema::hasColumn('leave_types', 'is_paid')) {
            $attributes['is_paid'] = true;
        }

        if (Schema::hasColumn('leave_types', 'status')) {
            $attributes['status'] = 'active';
        }

        if (Schema::hasColumn('leave_types', 'created_at')) {
            $attributes['created_at'] = now();
        }

        if (Schema::hasColumn('leave_types', 'updated_at')) {
            $attributes['updated_at'] = now();
        }

        return DB::table('leave_types')->insertGetId($attributes);
    }

    private function createLeaveRequest(
        Employee $employee,
        ?int $leaveTypeId,
        string $startDate,
        string $endDate,
        string $status = 'pending'
    ): LeaveRequest {
        $payload = $this->leavePayload($employee, $leaveTypeId, $startDate, $endDate, $status);

        return LeaveRequest::create(array_merge(
            $this->leaveDatabaseAssertion($payload),
            Schema::hasColumn('leave_requests', 'total_days')
                ? ['total_days' => $this->inclusiveDays($startDate, $endDate)]
                : [],
        ));
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
            'email' => "leave-employee-{$suffix}@example.com",
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

        if (Schema::hasColumn('employees', 'joining_date')) {
            $payload['joining_date'] = '2026-01-15';
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
                'joining_date',
                'employment_type',
                'status',
            ])
            ->all();
    }

    /**
     * @return array<string, string|int>
     */
    private function leavePayload(
        Employee $employee,
        ?int $leaveTypeId,
        string $startDate,
        string $endDate,
        string $status = 'pending'
    ): array {
        $payload = [];

        if (Schema::hasColumn('leave_requests', 'employee_id')) {
            $payload['employee_id'] = $employee->id;
        }

        if (Schema::hasColumn('leave_requests', 'leave_type_id') && $leaveTypeId !== null) {
            $payload['leave_type_id'] = $leaveTypeId;
        }

        if (Schema::hasColumn('leave_requests', 'leave_type')) {
            $payload['leave_type'] = 'annual';
        }

        if (Schema::hasColumn('leave_requests', 'type')) {
            $payload['type'] = 'annual';
        }

        if (Schema::hasColumn('leave_requests', 'start_date')) {
            $payload['start_date'] = $startDate;
        }

        if (Schema::hasColumn('leave_requests', 'end_date')) {
            $payload['end_date'] = $endDate;
        }

        if (Schema::hasColumn('leave_requests', 'status')) {
            $payload['status'] = $status;
        }

        if (Schema::hasColumn('leave_requests', 'reason')) {
            $payload['reason'] = "Leave {$status} test reason.";
        }

        if (Schema::hasColumn('leave_requests', 'remarks')) {
            $payload['remarks'] = "Leave {$status} test remarks.";
        }

        if (Schema::hasColumn('leave_requests', 'note')) {
            $payload['note'] = "Leave {$status} test note.";
        }

        if (Schema::hasColumn('leave_requests', 'rejection_reason')) {
            $payload['rejection_reason'] = null;
        }

        return $payload;
    }

    /**
     * @param array<string, string|int|null> $payload
     *
     * @return array<string, string|int|null>
     */
    private function leaveDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only([
                'employee_id',
                'leave_type_id',
                'leave_type',
                'type',
                'start_date',
                'end_date',
                'status',
                'reason',
                'remarks',
                'note',
                'rejection_reason',
            ])
            ->all();
    }

    private function inclusiveDays(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)
            ->startOfDay()
            ->diffInDays(Carbon::parse($endDate)->startOfDay()) + 1;
    }
}
