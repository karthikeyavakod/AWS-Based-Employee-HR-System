<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\CompanyAnnouncement;
use App\Models\Department;
use App\Models\DepartmentManager;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogObserver
{
    /**
     * @var array<class-string<Model>, array{module: string, fields: list<string>}>
     */
    private const AUDITED_MODELS = [
        User::class => ['module' => 'users', 'fields' => ['name', 'email', 'status', 'must_change_password']],
        Employee::class => ['module' => 'employees', 'fields' => ['employee_code', 'first_name', 'last_name', 'department_id', 'designation_id', 'employment_type', 'status']],
        Department::class => ['module' => 'departments', 'fields' => ['name', 'code', 'slug', 'status']],
        Designation::class => ['module' => 'designations', 'fields' => ['name', 'code', 'slug', 'department_id', 'status']],
        Attendance::class => ['module' => 'attendance', 'fields' => ['employee_id', 'attendance_date', 'check_in_at', 'check_out_at', 'status', 'work_minutes']],
        AttendanceCorrection::class => ['module' => 'attendance', 'fields' => ['employee_id', 'attendance_date', 'requested_check_in_at', 'requested_check_out_at', 'requested_status', 'status', 'reviewed_by', 'reviewed_at']],
        LeaveRequest::class => ['module' => 'leaves', 'fields' => ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'status', 'approved_by', 'approved_at']],
        Payroll::class => ['module' => 'payroll', 'fields' => ['payroll_run_id', 'employee_id', 'gross_salary', 'total_deductions', 'net_salary', 'status']],
        Role::class => ['module' => 'roles', 'fields' => ['name', 'slug']],
        CompanyAnnouncement::class => ['module' => 'announcements', 'fields' => ['title', 'status', 'publish_at', 'expires_at']],
        DepartmentManager::class => ['module' => 'department_managers', 'fields' => ['department_id', 'user_id']],
    ];

    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $this->safeValues($model, $this->fields($model)));
    }

    public function updated(Model $model): void
    {
        $fields = $this->fields($model);
        $changed = array_intersect($fields, array_keys($model->getChanges()));

        if ($changed === []) {
            if ($model instanceof User && array_key_exists('password', $model->getChanges())) {
                $this->record(
                    $model,
                    'updated',
                    [],
                    ['credential' => 'changed'],
                    'User credentials changed',
                );
            }

            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changed as $field) {
            $oldValues[$field] = $model->getRawOriginal($field);
            $newValues[$field] = $model->getAttribute($field);
        }

        $action = $this->statusAction($newValues);
        $this->record($model, $action, $oldValues, $newValues);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->safeValues($model, $this->fields($model)), []);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    private function record(
        Model $model,
        string $action,
        array $oldValues,
        array $newValues,
        ?string $description = null,
    ): void {
        if ($model instanceof AuditLog || Auth::user() === null) {
            return;
        }

        $definition = self::AUDITED_MODELS[$model::class] ?? null;

        if ($definition === null) {
            return;
        }

        app(AuditLogService::class)->log(
            $action,
            $definition['module'],
            $model->getKey() === null ? null : (int) $model->getKey(),
            class_basename($model),
            $oldValues,
            $newValues,
            $description ?? sprintf(
                '%s %s',
                Str::headline(class_basename($model)),
                Str::headline($action),
            ),
            subjectName: $this->subjectName($model),
        );
    }

    /**
     * @return list<string>
     */
    private function fields(Model $model): array
    {
        return self::AUDITED_MODELS[$model::class]['fields'] ?? [];
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function safeValues(Model $model, array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $model->getAttributes())) {
                $values[$field] = $model->getAttribute($field);
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $newValues
     */
    private function statusAction(array $newValues): string
    {
        return match ($newValues['status'] ?? null) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'updated',
        };
    }

    private function subjectName(Model $model): ?string
    {
        if ($model instanceof Employee) {
            return trim($model->first_name.' '.$model->last_name) ?: null;
        }

        if ($model instanceof User) {
            return $model->name;
        }

        if ($model instanceof DepartmentManager) {
            return $model->user?->name;
        }

        if ($model instanceof Attendance || $model instanceof AttendanceCorrection || $model instanceof LeaveRequest || $model instanceof Payroll) {
            $employee = $model->employee;

            return $employee === null
                ? null
                : (trim($employee->first_name.' '.$employee->last_name) ?: null);
        }

        if ($model instanceof Department || $model instanceof Designation || $model instanceof Role) {
            return $model->name;
        }

        if ($model instanceof CompanyAnnouncement) {
            return $model->title;
        }

        return null;
    }
}
