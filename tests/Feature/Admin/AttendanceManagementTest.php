<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_user_visiting_attendance_index_is_redirected_to_login(): void
    {
        $this->get('/admin/attendance')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_active_user_without_attendance_view_permission_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.attendance.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_attendance(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Attendance', 'Viewer');
        $attendance = $this->createAttendance($employee, '2026-02-01');
        $employeeName = $employee->user?->name
            ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        $this->actingAs($admin)
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSeeText('Attendance')
            ->assertSeeText($employeeName ?: 'N/A')
            ->assertSeeText($attendance->attendance_date?->format('M d, Y') ?? 'Feb 01, 2026');
    }

    public function test_super_admin_can_create_an_attendance_record(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Attendance', 'Creator');
        $payload = $this->attendancePayload($employee, '2026-02-02');

        $this->actingAs($admin)
            ->post(route('admin.attendance.store'), $payload)
            ->assertRedirect(route('admin.attendance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', $this->attendanceDatabaseAssertion($payload));

        if (Schema::hasColumn('attendances', 'work_minutes')) {
            $this->assertDatabaseHas('attendances', [
                'employee_id' => $employee->id,
                'attendance_date' => '2026-02-02',
                'work_minutes' => 480,
            ]);
        }
    }

    public function test_super_admin_can_update_an_attendance_record(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Attendance', 'Updater');
        $attendance = $this->createAttendance($employee, '2026-02-03');
        $payload = $this->attendancePayload($employee, '2026-02-04', 'late');

        $this->actingAs($admin)
            ->put(route('admin.attendance.update', $attendance), $payload)
            ->assertRedirect(route('admin.attendance.edit', $attendance))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', array_merge(
            ['id' => $attendance->id],
            $this->attendanceDatabaseAssertion($payload),
        ));
    }

    public function test_super_admin_can_delete_an_attendance_record(): void
    {
        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Attendance', 'Deleter');
        $attendance = $this->createAttendance($employee, '2026-02-05');

        $this->actingAs($admin)
            ->delete(route('admin.attendance.destroy', $attendance))
            ->assertRedirect(route('admin.attendance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_duplicate_attendance_for_same_employee_and_date_is_prevented(): void
    {
        if (
            ! Schema::hasColumn('attendances', 'employee_id')
            || ! Schema::hasColumn('attendances', 'attendance_date')
        ) {
            $this->markTestSkipped('Attendance duplicate prevention requires employee_id and attendance_date columns.');
        }

        $admin = $this->superAdmin();
        $employee = $this->createEmployee('Attendance', 'Duplicate');
        $payload = $this->attendancePayload($employee, '2026-02-06');

        $this->createAttendance($employee, '2026-02-06');

        $this->actingAs($admin)
            ->from(route('admin.attendance.create'))
            ->post(route('admin.attendance.store'), $payload)
            ->assertRedirect(route('admin.attendance.create'))
            ->assertSessionHasErrors('attendance_date');

        $this->assertSame(
            1,
            Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', '2026-02-06')
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
        $department = Department::create($this->departmentAttributes('Attendance Department'));
        $designation = Designation::create($this->designationAttributes('Attendance Designation', $department));
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

    private function createAttendance(Employee $employee, string $date): Attendance
    {
        $payload = $this->attendancePayload($employee, $date);

        return Attendance::create(array_merge(
            $this->attendanceDatabaseAssertion($payload),
            Schema::hasColumn('attendances', 'work_minutes') ? ['work_minutes' => 480] : [],
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
            'email' => "attendance-employee-{$suffix}@example.com",
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
    private function attendancePayload(Employee $employee, string $date, string $status = 'present'): array
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
            $payload['status'] = $status;
        }

        if (Schema::hasColumn('attendances', 'remarks')) {
            $payload['remarks'] = "Attendance {$status} test note.";
        }

        if (Schema::hasColumn('attendances', 'note')) {
            $payload['note'] = "Attendance {$status} test note.";
        }

        return $payload;
    }

    /**
     * @param array<string, string|int> $payload
     *
     * @return array<string, string|int>
     */
    private function attendanceDatabaseAssertion(array $payload): array
    {
        return collect($payload)
            ->only([
                'employee_id',
                'attendance_date',
                'date',
                'check_in_at',
                'check_in',
                'check_out_at',
                'check_out',
                'status',
                'remarks',
                'note',
            ])
            ->all();
    }
}
