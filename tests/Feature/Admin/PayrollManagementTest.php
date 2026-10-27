<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
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

class PayrollManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_payrolls_index_is_redirected_to_login(): void
    {
        $this->get('/admin/payrolls')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_payroll_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.payrolls.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_payrolls(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Payroll', 'Viewer');
        $payrollRunId = $this->createPayrollRun('View Payroll', 2026, 4);
        $payroll = $this->createPayroll($employee, $payrollRunId);
        $employeeName = $employee->user?->name
            ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        $this->actingAs($admin)
            ->get(route('admin.payrolls.index'))
            ->assertOk()
            ->assertSeeText('Payrolls')
            ->assertSeeText($employeeName ?: 'N/A')
            ->assertSeeText((string) $payroll->id);
    }

    public function test_super_admin_can_create_a_payroll_record(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Payroll', 'Creator');
        $payrollRunId = $this->createPayrollRun('Create Payroll', 2026, 5);
        $payload = $this->payrollPayload($employee, $payrollRunId);

        $this->actingAs($admin)
            ->post(route('admin.payrolls.store'), $payload)
            ->assertRedirect(route('admin.payrolls.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas($this->payrollTable(), $this->payrollDatabaseAssertion($payload));

        if (Schema::hasColumn($this->payrollTable(), 'net_salary')) {
            $this->assertDatabaseHas($this->payrollTable(), [
                'employee_id' => $employee->id,
                'net_salary' => $this->expectedNetSalary($payload),
            ]);
        }
    }

    public function test_super_admin_can_update_a_payroll_record(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Payroll', 'Updater');
        $payrollRunId = $this->createPayrollRun('Original Payroll', 2026, 6);
        $newPayrollRunId = $this->createPayrollRun('Updated Payroll', 2026, 7);
        $payroll = $this->createPayroll($employee, $payrollRunId);
        $payload = $this->payrollPayload($employee, $newPayrollRunId, 7200, 1100, 'paid');

        $this->actingAs($admin)
            ->put(route('admin.payrolls.update', $payroll), $payload)
            ->assertRedirect(route('admin.payrolls.edit', $payroll))
            ->assertSessionHas('success');

        $this->assertDatabaseHas($this->payrollTable(), array_merge(
            ['id' => $payroll->id],
            $this->payrollDatabaseAssertion($payload),
        ));
    }

    public function test_super_admin_can_delete_a_payroll_record(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Payroll', 'Deleter');
        $payrollRunId = $this->createPayrollRun('Delete Payroll', 2026, 8);
        $payroll = $this->createPayroll($employee, $payrollRunId);

        $this->actingAs($admin)
            ->delete(route('admin.payrolls.destroy', $payroll))
            ->assertRedirect(route('admin.payrolls.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing($this->payrollTable(), [
            'id' => $payroll->id,
        ]);
    }

    public function test_net_salary_is_calculated_correctly_when_supported(): void
    {
        if (! Schema::hasColumn($this->payrollTable(), 'net_salary')) {
            $this->markTestSkipped('Net salary calculation requires a net_salary column.');
        }

        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Payroll', 'Calculator');
        $payrollRunId = $this->createPayrollRun('Calculated Payroll', 2026, 9);
        $payload = $this->payrollPayload($employee, $payrollRunId, 6500, 725);

        $this->actingAs($admin)
            ->post(route('admin.payrolls.store'), $payload)
            ->assertRedirect(route('admin.payrolls.index'));

        $this->assertDatabaseHas($this->payrollTable(), [
            'employee_id' => $employee->id,
            'net_salary' => $this->expectedNetSalary($payload),
        ]);
    }

    public function test_duplicate_payroll_for_same_employee_and_pay_period_is_prevented(): void
    {
        if (! Schema::hasColumn($this->payrollTable(), 'employee_id') || ! $this->hasPayrollPeriodColumns()) {
            $this->markTestSkipped('Payroll duplicate prevention requires employee_id and a payroll period column.');
        }

        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Payroll', 'Duplicate');
        $payrollRunId = $this->createPayrollRun('Duplicate Payroll', 2026, 10);
        $payload = $this->payrollPayload($employee, $payrollRunId);

        $this->createPayroll($employee, $payrollRunId);

        $this->actingAs($admin)
            ->from(route('admin.payrolls.create'))
            ->post(route('admin.payrolls.store'), $payload)
            ->assertRedirect(route('admin.payrolls.create'))
            ->assertSessionHasErrors('employee_id');

        $query = Payroll::query()->where('employee_id', $employee->id);

        if (Schema::hasColumn($this->payrollTable(), 'payroll_run_id')) {
            $query->where('payroll_run_id', $payrollRunId);
        } elseif (Schema::hasColumn($this->payrollTable(), 'payroll_month')) {
            $query->where('payroll_month', $payload['payroll_month']);
        } else {
            $query
                ->where($this->monthColumn(), $payload[$this->monthColumn()])
                ->where($this->yearColumn(), $payload[$this->yearColumn()]);
        }

        $this->assertSame(1, $query->count());
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
        $department = Department::create($this->departmentAttributes('Payroll Department'));
        $designation = Designation::create($this->designationAttributes('Payroll Designation', $department));
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

    private function createPayrollRun(string $title, int $year, int $month): ?int
    {
        if (! Schema::hasTable('payroll_runs')) {
            return null;
        }

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

    private function createPayroll(Employee $employee, ?int $payrollRunId): Payroll
    {
        $payload = $this->payrollPayload($employee, $payrollRunId);

        return Payroll::create(array_merge(
            $this->payrollDatabaseAssertion($payload),
            Schema::hasColumn($this->payrollTable(), 'net_salary')
                ? ['net_salary' => $this->expectedNetSalary($payload)]
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
            'email' => "payroll-employee-{$suffix}@example.com",
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
     * @return array<string, string|int|float|null>
     */
    private function payrollPayload(
        Employee $employee,
        ?int $payrollRunId,
        float $salary = 5000,
        float $deductions = 500,
        string $status = 'draft'
    ): array {
        $payload = [];

        if (Schema::hasColumn($this->payrollTable(), 'payroll_run_id') && $payrollRunId !== null) {
            $payload['payroll_run_id'] = $payrollRunId;
        }

        if (Schema::hasColumn($this->payrollTable(), 'employee_id')) {
            $payload['employee_id'] = $employee->id;
        }

        if (Schema::hasColumn($this->payrollTable(), 'payroll_month')) {
            $payload['payroll_month'] = '2026-10';
        }

        if ($this->monthColumn() !== null) {
            $payload[$this->monthColumn()] = 10;
        }

        if ($this->yearColumn() !== null) {
            $payload[$this->yearColumn()] = 2026;
        }

        if ($this->salaryColumn() !== null) {
            $payload[$this->salaryColumn()] = $salary;
        }

        if ($this->allowanceColumn() !== null) {
            $payload[$this->allowanceColumn()] = 250;
        }

        if ($this->deductionColumn() !== null) {
            $payload[$this->deductionColumn()] = $deductions;
        }

        if (Schema::hasColumn($this->payrollTable(), 'status')) {
            $payload['status'] = $status;
        }

        if (Schema::hasColumn($this->payrollTable(), 'payment_status')) {
            $payload['payment_status'] = $status;
        }

        if (Schema::hasColumn($this->payrollTable(), 'payment_date')) {
            $payload['payment_date'] = '2026-10-28';
        }

        if (Schema::hasColumn($this->payrollTable(), 'paid_at')) {
            $payload['paid_at'] = '2026-10-28 10:00:00';
        }

        return $payload;
    }

    /**
     * @param array<string, string|int|float|null> $payload
     *
     * @return array<string, string|int|float|null>
     */
    private function payrollDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only([
                'payroll_run_id',
                'employee_id',
                'payroll_month',
                'month',
                'year',
                'period_month',
                'period_year',
                'basic_salary',
                'salary',
                'gross_salary',
                'allowance',
                'allowances',
                'total_allowances',
                'deduction',
                'deductions',
                'total_deductions',
                'status',
                'payment_status',
                'payment_date',
                'paid_at',
            ])
            ->all();
    }

    /**
     * @param array<string, string|int|float|null> $payload
     */
    private function expectedNetSalary(array $payload): float
    {
        if (
            Schema::hasColumn($this->payrollTable(), 'gross_salary')
            && Schema::hasColumn($this->payrollTable(), 'total_deductions')
        ) {
            return (float) ($payload['gross_salary'] ?? 0) - (float) ($payload['total_deductions'] ?? 0);
        }

        $salary = $this->salaryColumn() === null ? 0 : (float) ($payload[$this->salaryColumn()] ?? 0);
        $allowances = $this->allowanceColumn() === null ? 0 : (float) ($payload[$this->allowanceColumn()] ?? 0);
        $deductions = $this->deductionColumn() === null ? 0 : (float) ($payload[$this->deductionColumn()] ?? 0);

        return $salary + $allowances - $deductions;
    }

    private function hasPayrollPeriodColumns(): bool
    {
        return Schema::hasColumn($this->payrollTable(), 'payroll_run_id')
            || Schema::hasColumn($this->payrollTable(), 'payroll_month')
            || ($this->monthColumn() !== null && $this->yearColumn() !== null);
    }

    private function payrollTable(): string
    {
        return (new Payroll())->getTable();
    }

    private function monthColumn(): ?string
    {
        return match (true) {
            Schema::hasColumn($this->payrollTable(), 'month') => 'month',
            Schema::hasColumn($this->payrollTable(), 'period_month') => 'period_month',
            default => null,
        };
    }

    private function yearColumn(): ?string
    {
        return match (true) {
            Schema::hasColumn($this->payrollTable(), 'year') => 'year',
            Schema::hasColumn($this->payrollTable(), 'period_year') => 'period_year',
            default => null,
        };
    }

    private function salaryColumn(): ?string
    {
        return match (true) {
            Schema::hasColumn($this->payrollTable(), 'basic_salary') => 'basic_salary',
            Schema::hasColumn($this->payrollTable(), 'salary') => 'salary',
            Schema::hasColumn($this->payrollTable(), 'gross_salary') => 'gross_salary',
            default => null,
        };
    }

    private function allowanceColumn(): ?string
    {
        return match (true) {
            Schema::hasColumn($this->payrollTable(), 'allowance') => 'allowance',
            Schema::hasColumn($this->payrollTable(), 'allowances') => 'allowances',
            Schema::hasColumn($this->payrollTable(), 'total_allowances') => 'total_allowances',
            default => null,
        };
    }

    private function deductionColumn(): ?string
    {
        return match (true) {
            Schema::hasColumn($this->payrollTable(), 'deduction') => 'deduction',
            Schema::hasColumn($this->payrollTable(), 'deductions') => 'deductions',
            Schema::hasColumn($this->payrollTable(), 'total_deductions') => 'total_deductions',
            default => null,
        };
    }
}
