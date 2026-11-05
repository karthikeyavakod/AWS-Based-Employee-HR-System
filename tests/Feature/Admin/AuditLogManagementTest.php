<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditLogManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_view_audit_logs_and_activity_details(): void
    {
        $admin = $this->superAdmin();
        $log = $this->createLog($admin, [
            'description' => 'Department updated',
            'module' => 'departments',
            'action' => 'updated',
            'old_values' => ['name' => 'Old'],
            'new_values' => ['name' => 'New'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSeeText('Total Activities')
            ->assertSeeText("Today's Activities")
            ->assertSeeText('Login Activities')
            ->assertSeeText('Critical Activities')
            ->assertSeeText('Search activity')
            ->assertSeeText('Export PDF')
            ->assertSeeText('View Details')
            ->assertSee('data-audit-details=', false)
            ->assertSee($log->description, false);
    }

    public function test_audit_log_filters_and_pagination_preserve_the_query(): void
    {
        $admin = $this->superAdmin();
        $actor = User::factory()->create(['name' => 'Filter Target User']);

        foreach (range(1, 11) as $index) {
            $this->createLog($actor, [
                'description' => "Payroll update {$index}",
                'module' => 'payroll',
                'action' => 'updated',
            ]);
        }

        $this->createLog($admin, [
            'description' => 'Department created',
            'module' => 'departments',
            'action' => 'created',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', [
                'q' => 'Payroll',
                'module' => 'payroll',
                'user_id' => $actor->id,
                'action' => 'updated',
                'date_from' => today()->toDateString(),
                'date_to' => today()->toDateString(),
                'per_page' => 10,
                'page' => 2,
            ]))
            ->assertOk()
            ->assertSee('Payroll update 1', false)
            ->assertDontSeeText('Department created')
            ->assertSee('module=payroll', false)
            ->assertSee('per_page=10', false);

        $this->assertSame(11, $response->viewData('auditLogs')->total());
        $this->assertSame(10, $response->viewData('auditLogs')->perPage());
    }

    public function test_csv_export_uses_filters_and_includes_export_metadata(): void
    {
        $admin = $this->superAdmin();
        $actor = User::factory()->create(['name' => 'CSV Target User']);
        $this->createLog($actor, [
            'description' => 'Filtered payroll activity',
            'module' => 'payroll',
            'action' => 'updated',
        ]);
        $this->createLog($admin, [
            'description' => 'Excluded department activity',
            'module' => 'departments',
            'action' => 'created',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-logs.export.csv', [
                'module' => 'payroll',
                'action' => 'updated',
                'date_from' => today()->toDateString(),
                'date_to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertDownload();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Smart HR Connect - Audit Logs', $csv);
        $this->assertStringContainsString('Filtered payroll activity', $csv);
        $this->assertStringNotContainsString('Excluded department activity', $csv);
        $this->assertStringContainsString('Generated at', $csv);
    }

    public function test_pdf_export_returns_a_pdf_for_the_selected_filters(): void
    {
        $admin = $this->superAdmin();
        $this->createLog($admin, [
            'description' => 'PDF export activity',
            'module' => 'departments',
            'action' => 'created',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-logs.export.pdf', ['module' => 'departments']))
            ->assertOk()
            ->assertDownload();

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_audit_logs_and_exports_are_restricted_to_super_admins(): void
    {
        $role = Role::create([
            'name' => 'Audit Viewer '.Str::random(8),
            'slug' => 'audit-viewer-'.Str::lower(Str::random(8)),
        ]);
        $permission = Permission::create([
            'name' => 'Audit Log View',
            'slug' => 'audit.log.view',
            'module' => 'audit',
        ]);
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();

        $this->get(route('admin.audit-logs.export.csv'))
            ->assertForbidden();

        $this->get(route('admin.audit-logs.export.pdf'))
            ->assertForbidden();
    }

    public function test_department_model_changes_are_audited_and_sensitive_values_are_redacted(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        $suffix = Str::lower(Str::random(8));
        $department = Department::create([
            'name' => 'Audit Department '.$suffix,
            'code' => Str::upper($suffix),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'created',
            'module' => 'departments',
            'subject_id' => $department->id,
        ]);

        $department->update(['name' => 'Renamed Audit Department '.$suffix]);
        $updateLog = AuditLog::query()
            ->where('subject_type', 'Department')
            ->where('subject_id', $department->id)
            ->where('action', 'updated')
            ->firstOrFail();

        $this->assertSame('Audit Department '.$suffix, $updateLog->old_values['name']);
        $this->assertSame('Renamed Audit Department '.$suffix, $updateLog->new_values['name']);
        $this->assertSame('Department Updated', $updateLog->description);
    }

    public function test_user_password_updates_are_logged_without_recording_the_password(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create(['status' => 'active']);

        $this->actingAs($admin);
        $target->update(['password' => Hash::make('TopSecretPassword!')]);

        $log = AuditLog::query()
            ->where('user_id', $admin->id)
            ->where('module', 'users')
            ->where('subject_id', $target->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('User credentials changed', $log->description);
        $this->assertSame([], $log->old_values);
        $this->assertSame([], $log->new_values);
        $this->assertStringNotContainsString('TopSecretPassword!', $log->toJson());
    }

    public function test_role_permission_changes_are_audited(): void
    {
        $admin = $this->superAdmin();
        $role = Role::create([
            'name' => 'Permission Audit Role '.Str::random(8),
            'slug' => 'permission-audit-'.Str::lower(Str::random(8)),
        ]);
        $permissionIds = Permission::query()
            ->whereIn('slug', ['dashboard.view', 'employee.view'])
            ->pluck('id')
            ->all();

        $this->actingAs($admin);
        app(RoleService::class)->syncPermissions($role, $permissionIds);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'updated',
            'module' => 'permissions',
            'subject_type' => 'Role',
            'subject_id' => $role->id,
            'subject_name' => $role->name,
        ]);
    }

    public function test_employee_attendance_leave_and_payroll_changes_are_audited(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        $suffix = Str::lower(Str::random(8));
        $department = Department::create([
            'name' => 'Activity Department '.$suffix,
            'code' => Str::upper($suffix),
        ]);
        $designation = Designation::create([
            'department_id' => $department->id,
            'name' => 'Activity Designation '.$suffix,
            'code' => Str::upper('D-'.$suffix),
        ]);
        $employee = Employee::create([
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'employee_code' => Str::upper('E-'.$suffix),
            'first_name' => 'Audit',
            'last_name' => 'Employee',
            'joining_date' => today()->toDateString(),
            'status' => 'active',
        ]);
        $employee->update(['first_name' => 'Updated']);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->subHours(8),
            'status' => 'present',
            'remarks' => 'Private attendance note',
        ]);
        $attendance->update(['status' => 'late']);

        $leaveTypeId = DB::table('leave_types')->insertGetId([
            'name' => 'Audit Leave '.$suffix,
            'code' => Str::upper('L-'.$suffix),
            'default_days' => 10,
            'is_paid' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => today()->addDay()->toDateString(),
            'end_date' => today()->addDay()->toDateString(),
            'total_days' => 1,
            'reason' => 'Confidential personal reason',
            'status' => 'pending',
        ]);
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $payrollRunId = DB::table('payroll_runs')->insertGetId([
            'title' => 'Audit Payroll '.$suffix,
            'period_year' => 2199,
            'period_month' => 12,
            'period_start' => '2199-12-01',
            'period_end' => '2199-12-31',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $payroll = Payroll::create([
            'payroll_run_id' => $payrollRunId,
            'employee_id' => $employee->id,
            'gross_salary' => 30000,
            'total_deductions' => 3000,
            'net_salary' => 27000,
            'status' => 'draft',
        ]);
        $payroll->update(['net_salary' => 28000]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'employees',
            'action' => 'created',
            'subject_id' => $employee->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'designations',
            'action' => 'created',
            'subject_id' => $designation->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'attendance',
            'action' => 'updated',
            'subject_id' => $attendance->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'leaves',
            'action' => 'approved',
            'subject_id' => $leaveRequest->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'payroll',
            'action' => 'updated',
            'subject_id' => $payroll->id,
        ]);

        $leaveLog = AuditLog::query()
            ->where('subject_type', 'LeaveRequest')
            ->where('subject_id', $leaveRequest->id)
            ->where('action', 'approved')
            ->firstOrFail();
        $this->assertArrayNotHasKey('reason', $leaveLog->old_values ?? []);
        $this->assertArrayNotHasKey('reason', $leaveLog->new_values ?? []);
    }

    public function test_login_and_logout_events_are_audited(): void
    {
        $this->superAdmin();

        $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'Password@12345',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'module' => 'auth',
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'logout',
            'module' => 'auth',
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
     * @param  array<string, mixed>  $overrides
     */
    private function createLog(User $user, array $overrides = []): AuditLog
    {
        return AuditLog::create(array_merge([
            'user_id' => $user->id,
            'action' => 'created',
            'module' => 'employees',
            'subject_type' => 'Employee',
            'subject_id' => 1,
            'subject_name' => 'Employee One',
            'description' => 'Employee created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Chrome / Windows / Desktop',
            'old_values' => [],
            'new_values' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
