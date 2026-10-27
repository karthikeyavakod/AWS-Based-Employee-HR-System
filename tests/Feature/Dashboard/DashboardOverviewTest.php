<?php

namespace Tests\Feature\Dashboard;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Permission;
use App\Models\Role;
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

class DashboardOverviewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_dashboard_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_dashboard_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_dashboard_overview(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Total Employees')
            ->assertSeeText('Total Departments')
            ->assertSeeText('Total Designations')
            ->assertSeeText('Today Attendance')
            ->assertSeeText('Pending Leave Requests')
            ->assertSeeText('Current Month Payrolls');
    }

    public function test_dashboard_displays_created_data_counts(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Dashboard Department'));
        $designation = Designation::create($this->designationAttributes('Dashboard Designation', $department));
        $this->createEmployee('Dashboard', 'Counter', $department, $designation);

        $response = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Total Employees')
            ->assertSeeText('Total Departments')
            ->assertSeeText('Total Designations');

        $response->assertSeeText(number_format(Employee::query()->count()));
        $response->assertSeeText(number_format(Department::query()->count()));
        $response->assertSeeText(number_format(Designation::query()->count()));
    }

    public function test_dashboard_displays_recent_sections(): void
    {
        $admin = $this->superAdmin();
        $department = Department::create($this->departmentAttributes('Recent Department'));
        $designation = Designation::create($this->designationAttributes('Recent Designation', $department));
        $employee = $this->createEmployee('Recent', 'Employee', $department, $designation);

        if (class_exists(LeaveRequest::class) && Schema::hasTable((new LeaveRequest())->getTable())) {
            $leaveTypeId = $this->createLeaveType('Dashboard Leave');
            $this->createLeaveRequest($employee, $leaveTypeId);
        }

        if (class_exists(Payroll::class) && Schema::hasTable($this->payrollTable())) {
            $payrollRunId = $this->createPayrollRun('Dashboard Payroll');
            $this->createPayroll($employee, $payrollRunId);
        }

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Recent Employees')
            ->assertSeeText('Recent Leave Requests')
            ->assertSeeText('Recent Payrolls');
    }

    public function test_dashboard_quick_links_are_permission_aware(): void
    {
        $user = $this->dashboardOnlyUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Total Employees')
            ->assertDontSee(route('admin.employees.index'), false)
            ->assertDontSee(route('admin.departments.index'), false)
            ->assertDontSee(route('admin.designations.index'), false)
            ->assertDontSee(route('admin.attendance.index'), false)
            ->assertDontSee(route('admin.leaves.index'), false)
            ->assertDontSee(route('admin.payrolls.index'), false)
            ->assertDontSee(route('admin.roles.index'), false);
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

    private function dashboardOnlyUser(): User
    {
        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        $permission = Permission::where('slug', 'dashboard.view')->firstOrFail();
        $role = Role::create([
            'name' => 'Dashboard Only '.Str::lower(Str::random(8)),
            'slug' => 'dashboard-only-'.Str::lower(Str::random(8)),
            'description' => 'Dashboard-only test role.',
        ]);

        $role->permissions()->sync([$permission->id]);

        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->roles()->sync([$role->id]);

        return $user;
    }

    private function createEmployee(
        string $firstName,
        string $lastName,
        Department $department,
        Designation $designation
    ): Employee {
        $payload = $this->employeePayload($firstName, $lastName, $department, $designation);
        $attributes = $this->employeeDatabaseAttributes($payload);

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

    private function createLeaveRequest(Employee $employee, ?int $leaveTypeId): ?LeaveRequest
    {
        $table = (new LeaveRequest())->getTable();
        $payload = [];

        if (Schema::hasColumn($table, 'employee_id')) {
            $payload['employee_id'] = $employee->id;
        }

        if (Schema::hasColumn($table, 'leave_type_id') && $leaveTypeId !== null) {
            $payload['leave_type_id'] = $leaveTypeId;
        }

        if (Schema::hasColumn($table, 'start_date')) {
            $payload['start_date'] = '2026-04-01';
        }

        if (Schema::hasColumn($table, 'end_date')) {
            $payload['end_date'] = '2026-04-02';
        }

        if (Schema::hasColumn($table, 'total_days')) {
            $payload['total_days'] = 2;
        }

        if (Schema::hasColumn($table, 'reason')) {
            $payload['reason'] = 'Dashboard recent leave test.';
        }

        if (Schema::hasColumn($table, 'status')) {
            $payload['status'] = 'pending';
        }

        if ($payload === []) {
            return null;
        }

        return LeaveRequest::create($payload);
    }

    private function createPayrollRun(string $title): ?int
    {
        if (! Schema::hasTable('payroll_runs')) {
            return null;
        }

        [$year, $month] = $this->unusedPayrollRunPeriod();
        $attributes = [];

        if (Schema::hasColumn('payroll_runs', 'title')) {
            $attributes['title'] = $title.' '.Str::lower(Str::random(8));
        }

        if (Schema::hasColumn('payroll_runs', 'period_year')) {
            $attributes['period_year'] = $year;
        }

        if (Schema::hasColumn('payroll_runs', 'period_month')) {
            $attributes['period_month'] = $month;
        }

        if (Schema::hasColumn('payroll_runs', 'period_start')) {
            $attributes['period_start'] = sprintf('%04d-%02d-01', $year, $month);
        }

        if (Schema::hasColumn('payroll_runs', 'period_end')) {
            $attributes['period_end'] = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        }

        if (Schema::hasColumn('payroll_runs', 'status')) {
            $attributes['status'] = 'draft';
        }

        if (Schema::hasColumn('payroll_runs', 'payment_date')) {
            $attributes['payment_date'] = sprintf('%04d-%02d-28', $year, $month);
        }

        if (Schema::hasColumn('payroll_runs', 'created_at')) {
            $attributes['created_at'] = now();
        }

        if (Schema::hasColumn('payroll_runs', 'updated_at')) {
            $attributes['updated_at'] = now();
        }

        return DB::table('payroll_runs')->insertGetId($attributes);
    }

    private function createPayroll(Employee $employee, ?int $payrollRunId): ?Payroll
    {
        if (! Schema::hasTable($this->payrollTable())) {
            return null;
        }

        $payload = [];

        if (Schema::hasColumn($this->payrollTable(), 'payroll_run_id') && $payrollRunId !== null) {
            $payload['payroll_run_id'] = $payrollRunId;
        }

        if (Schema::hasColumn($this->payrollTable(), 'employee_id')) {
            $payload['employee_id'] = $employee->id;
        }

        if (Schema::hasColumn($this->payrollTable(), 'gross_salary')) {
            $payload['gross_salary'] = 5000;
        }

        if (Schema::hasColumn($this->payrollTable(), 'total_deductions')) {
            $payload['total_deductions'] = 500;
        }

        if (Schema::hasColumn($this->payrollTable(), 'net_salary')) {
            $payload['net_salary'] = 4500;
        }

        if (Schema::hasColumn($this->payrollTable(), 'status')) {
            $payload['status'] = 'draft';
        }

        if ($payload === []) {
            return null;
        }

        return Payroll::create($payload);
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
            'email' => "dashboard-employee-{$suffix}@example.com",
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
    private function employeeDatabaseAttributes(array $payload): array
    {
        return collect($payload)
            ->only([
                'department_id',
                'designation_id',
                'employee_code',
                'first_name',
                'last_name',
                'joining_date',
                'employment_type',
                'status',
            ])
            ->all();
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function unusedPayrollRunPeriod(): array
    {
        for ($year = 2090; $year <= 2199; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                if (
                    ! Schema::hasColumn('payroll_runs', 'period_year')
                    || ! Schema::hasColumn('payroll_runs', 'period_month')
                    || ! DB::table('payroll_runs')
                        ->where('period_year', $year)
                        ->where('period_month', $month)
                        ->exists()
                ) {
                    return [$year, $month];
                }
            }
        }

        return [2199, 12];
    }

    private function payrollTable(): string
    {
        return (new Payroll())->getTable();
    }
}
