<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
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

class ReportManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_reports_index_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_report_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_reports_landing_page(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSeeText('Reports')
            ->assertSeeText('Employee Report')
            ->assertSeeText('Attendance Report')
            ->assertSeeText('Leave Report')
            ->assertSeeText('Payroll Report');
    }

    public function test_super_admin_can_view_employee_report(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Report', 'Employee');
        $employeeName = $this->employeeName($employee);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.employees'))
            ->assertOk()
            ->assertSeeText('Employee Report')
            ->assertSeeText($employeeName ?: 'N/A');

        if (Schema::hasColumn('employees', 'user_id') && $employee->user?->email) {
            $response->assertSeeText($employee->user->email);
        }
    }

    public function test_super_admin_can_view_attendance_report(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Report', 'Attendance');
        $this->createAttendance($employee, '2026-05-01');

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.attendance'))
            ->assertOk()
            ->assertSeeText('Attendance Report');

        if (Schema::hasColumn('attendances', 'attendance_date') || Schema::hasColumn('attendances', 'date')) {
            $response->assertSeeText('May 01, 2026');
        } else {
            $response->assertSeeText($this->employeeName($employee) ?: 'N/A');
        }
    }

    public function test_super_admin_can_view_leave_report(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Report', 'Leave');
        $leaveTypeId = $this->createLeaveType('Report Leave');
        $this->createLeaveRequest($employee, $leaveTypeId, '2026-06-01', '2026-06-03');

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.leaves'))
            ->assertOk()
            ->assertSeeText('Leave Report');

        if (Schema::hasColumn('leave_requests', 'start_date')) {
            $response->assertSeeText('Jun 01, 2026');
        } elseif (Schema::hasColumn('leave_requests', 'status')) {
            $response->assertSeeText('Pending');
        } else {
            $response->assertSeeText($this->employeeName($employee) ?: 'N/A');
        }
    }

    public function test_super_admin_can_view_payroll_report(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Report', 'Payroll');
        $payrollRunId = $this->createPayrollRun('Report Payroll');
        $payroll = $this->createPayroll($employee, $payrollRunId);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.payrolls'))
            ->assertOk()
            ->assertSeeText('Payroll Report')
            ->assertSeeText($this->employeeName($employee) ?: 'N/A');

        if ($payroll !== null && Schema::hasColumn($this->payrollTable(), 'net_salary')) {
            $response->assertSeeText(number_format((float) $payroll->net_salary, 2));
        }
    }

    public function test_report_filters_validate_date_range(): void
    {
        $admin = $this->superAdmin();
        $invalidDates = [
            'from_date' => '2026-07-10',
            'to_date' => '2026-07-01',
        ];

        $this->actingAs($admin)
            ->from(route('admin.reports.attendance'))
            ->get(route('admin.reports.attendance', $invalidDates))
            ->assertRedirect(route('admin.reports.attendance'))
            ->assertSessionHasErrors('to_date');

        $this->actingAs($admin)
            ->from(route('admin.reports.leaves'))
            ->get(route('admin.reports.leaves', $invalidDates))
            ->assertRedirect(route('admin.reports.leaves'))
            ->assertSessionHasErrors('to_date');
    }

    public function test_reports_are_read_only(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Read Only', 'Employee');
        $attendance = $this->createAttendance($employee, '2026-08-01');
        $leaveTypeId = $this->createLeaveType('Read Only Leave');
        $leaveRequest = $this->createLeaveRequest($employee, $leaveTypeId, '2026-08-02', '2026-08-03');
        $payrollRunId = $this->createPayrollRun('Read Only Payroll');
        $payroll = $this->createPayroll($employee, $payrollRunId);

        $prohibitedUrlsByRoute = [
            route('admin.reports.index') => [
                route('admin.employees.create'),
                route('admin.attendance.create'),
                route('admin.leaves.create'),
                route('admin.payrolls.create'),
            ],
            route('admin.reports.employees') => [
                route('admin.employees.create'),
                route('admin.employees.edit', $employee),
                route('admin.employees.destroy', $employee),
            ],
            route('admin.reports.attendance') => [
                route('admin.attendance.create'),
                route('admin.attendance.edit', $attendance),
                route('admin.attendance.destroy', $attendance),
            ],
            route('admin.reports.leaves') => [
                route('admin.leaves.create'),
                route('admin.leaves.edit', $leaveRequest),
                route('admin.leaves.destroy', $leaveRequest),
                route('admin.leaves.approve', $leaveRequest),
                route('admin.leaves.reject', $leaveRequest),
            ],
            route('admin.reports.payrolls') => array_filter([
                route('admin.payrolls.create'),
                $payroll ? route('admin.payrolls.edit', $payroll) : null,
                $payroll ? route('admin.payrolls.destroy', $payroll) : null,
            ]),
        ];

        foreach ($prohibitedUrlsByRoute as $reportRoute => $prohibitedUrls) {
            $response = $this->actingAs($admin)
                ->get($reportRoute)
                ->assertOk();

            foreach ($prohibitedUrls as $prohibitedUrl) {
                $response->assertDontSee($prohibitedUrl, false);
            }
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

    private function createEmployee(string $firstName, string $lastName): Employee
    {
        $department = Department::create($this->departmentAttributes('Report Department'));
        $designation = Designation::create($this->designationAttributes('Report Designation', $department));
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

    private function createAttendance(Employee $employee, string $date): Attendance
    {
        $payload = [];

        if (Schema::hasColumn('attendances', 'employee_id')) {
            $payload['employee_id'] = $employee->id;
        }

        if (Schema::hasColumn('attendances', 'attendance_date')) {
            $payload['attendance_date'] = $date;
        }

        if (Schema::hasColumn('attendances', 'date')) {
            $payload['date'] = $date;
        }

        if (Schema::hasColumn('attendances', 'check_in_at')) {
            $payload['check_in_at'] = "{$date} 09:00:00";
        }

        if (Schema::hasColumn('attendances', 'check_in')) {
            $payload['check_in'] = '09:00:00';
        }

        if (Schema::hasColumn('attendances', 'check_out_at')) {
            $payload['check_out_at'] = "{$date} 17:00:00";
        }

        if (Schema::hasColumn('attendances', 'check_out')) {
            $payload['check_out'] = '17:00:00';
        }

        if (Schema::hasColumn('attendances', 'status')) {
            $payload['status'] = 'present';
        }

        if (Schema::hasColumn('attendances', 'work_minutes')) {
            $payload['work_minutes'] = 480;
        }

        if (Schema::hasColumn('attendances', 'remarks')) {
            $payload['remarks'] = 'Report attendance note.';
        }

        if (Schema::hasColumn('attendances', 'note')) {
            $payload['note'] = 'Report attendance note.';
        }

        return Attendance::create($payload);
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
        string $endDate
    ): LeaveRequest {
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

        if (Schema::hasColumn('leave_requests', 'total_days')) {
            $payload['total_days'] = $this->inclusiveDays($startDate, $endDate);
        }

        if (Schema::hasColumn('leave_requests', 'status')) {
            $payload['status'] = 'pending';
        }

        if (Schema::hasColumn('leave_requests', 'reason')) {
            $payload['reason'] = 'Report leave reason.';
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

        if (Schema::hasColumn($this->payrollTable(), 'payroll_month')) {
            $payload['payroll_month'] = '2026-08';
        }

        if (Schema::hasColumn($this->payrollTable(), 'month')) {
            $payload['month'] = 8;
        }

        if (Schema::hasColumn($this->payrollTable(), 'year')) {
            $payload['year'] = 2026;
        }

        if (Schema::hasColumn($this->payrollTable(), 'gross_salary')) {
            $payload['gross_salary'] = 5000;
        }

        if (Schema::hasColumn($this->payrollTable(), 'basic_salary')) {
            $payload['basic_salary'] = 5000;
        }

        if (Schema::hasColumn($this->payrollTable(), 'salary')) {
            $payload['salary'] = 5000;
        }

        if (Schema::hasColumn($this->payrollTable(), 'allowance')) {
            $payload['allowance'] = 250;
        }

        if (Schema::hasColumn($this->payrollTable(), 'allowances')) {
            $payload['allowances'] = 250;
        }

        if (Schema::hasColumn($this->payrollTable(), 'total_deductions')) {
            $payload['total_deductions'] = 500;
        }

        if (Schema::hasColumn($this->payrollTable(), 'deduction')) {
            $payload['deduction'] = 500;
        }

        if (Schema::hasColumn($this->payrollTable(), 'deductions')) {
            $payload['deductions'] = 500;
        }

        if (Schema::hasColumn($this->payrollTable(), 'net_salary')) {
            $payload['net_salary'] = 4500;
        }

        if (Schema::hasColumn($this->payrollTable(), 'status')) {
            $payload['status'] = 'draft';
        }

        if (Schema::hasColumn($this->payrollTable(), 'payment_status')) {
            $payload['payment_status'] = 'draft';
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
            'email' => "report-employee-{$suffix}@example.com",
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
    private function employeeDatabaseAttributes(array $payload): array
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

    private function employeeName(Employee $employee): string
    {
        return $employee->user?->name
            ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
    }

    private function inclusiveDays(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)
            ->startOfDay()
            ->diffInDays(Carbon::parse($endDate)->startOfDay()) + 1;
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
