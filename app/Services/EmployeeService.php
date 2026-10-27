<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeeService
{
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data): Employee {
            $payload = $this->prepareEmployeeData($data);

            if (Schema::hasColumn('employees', 'user_id')) {
                $user = $this->createOrUpdateUser(null, $data);
                $this->assignEmployeeRole($user);
                $payload['user_id'] = $user->id;
            }

            return Employee::create($payload);
        });
    }

    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data): Employee {
            $payload = $this->prepareEmployeeData($data, $employee);

            if (Schema::hasColumn('employees', 'user_id')) {
                $user = $this->createOrUpdateUser($employee->user, $data);
                $this->assignEmployeeRole($user);
                $payload['user_id'] = $user->id;
            }

            $employee->update($payload);

            return $employee->refresh();
        });
    }

    public function delete(Employee $employee): bool
    {
        foreach ($this->protectedRelatedTables() as $table) {
            if ($this->hasRelatedRecords($table, $employee)) {
                throw new DomainException('This employee has related records and cannot be deleted.');
            }
        }

        return DB::transaction(function () use ($employee): bool {
            $user = $employee->user;
            $deleted = (bool) $employee->delete();

            if ($user !== null && Schema::hasColumn('users', 'status')) {
                $user->update(['status' => 'inactive']);
            }

            return $deleted;
        });
    }

    private function createOrUpdateUser(?User $user, array $data): User
    {
        $payload = [
            'name' => $data['name'] ?? $this->employeeDisplayName($data),
            'email' => $data['email'],
            'status' => Schema::hasColumn('users', 'status') ? 'active' : null,
        ];

        $payload = array_filter($payload, fn ($value): bool => $value !== null);

        if ($user === null || blank($user->password) || filled($data['password'] ?? null)) {
            $payload['password'] = Hash::make($data['password'] ?? 'Password@12345');
        }

        if ($user === null) {
            return User::create($payload);
        }

        $user->update($payload);

        return $user->refresh();
    }

    private function assignEmployeeRole(User $user): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_user')) {
            return;
        }

        $role = Role::where('slug', 'employee')
            ->orWhere('name', 'Employee')
            ->first();

        if ($role !== null) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    private function prepareEmployeeData(array $data, ?Employee $employee = null): array
    {
        $payload = Arr::only($data, [
            'user_id',
            'department_id',
            'designation_id',
            'employee_code',
            'code',
            'slug',
            'first_name',
            'last_name',
            'phone',
            'gender',
            'date_of_birth',
            'joining_date',
            'address',
            'employment_type',
            'status',
        ]);

        foreach (array_keys($payload) as $column) {
            if (! Schema::hasColumn('employees', $column)) {
                unset($payload[$column]);
            }
        }

        $this->fillGeneratedIdentifier($payload, 'employee_code', $employee);
        $this->fillGeneratedIdentifier($payload, 'code', $employee);
        $this->fillGeneratedIdentifier($payload, 'slug', $employee);

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function fillGeneratedIdentifier(array &$payload, string $column, ?Employee $employee = null): void
    {
        if (! Schema::hasColumn('employees', $column)) {
            return;
        }

        $shouldGenerate = $employee === null
            ? blank($payload[$column] ?? null)
            : array_key_exists($column, $payload) && blank($payload[$column]);

        if (! $shouldGenerate) {
            return;
        }

        $payload[$column] = $this->uniqueIdentifier($this->employeeDisplayName($payload), $column, $employee);
    }

    private function uniqueIdentifier(string $name, string $column, ?Employee $employee = null): string
    {
        $baseIdentifier = $column === 'slug'
            ? Str::slug($name)
            : Str::upper(Str::slug($name, '-'));

        if ($baseIdentifier === '') {
            $baseIdentifier = $column === 'slug' ? 'employee' : 'EMPLOYEE';
        }

        $identifier = $baseIdentifier;
        $counter = 2;

        while (
            Employee::withTrashed()
                ->where($column, $identifier)
                ->when($employee !== null, fn ($query) => $query->whereKeyNot($employee->getKey()))
                ->exists()
        ) {
            $identifier = "{$baseIdentifier}-{$counter}";
            $counter++;
        }

        return $identifier;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function employeeDisplayName(array $data): string
    {
        return trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')) ?: 'Employee';
    }

    /**
     * @return list<string>
     */
    private function protectedRelatedTables(): array
    {
        return [
            'attendances',
            'leave_requests',
            'leave_balances',
            'employee_salary_structures',
            'payslips',
        ];
    }

    private function hasRelatedRecords(string $table, Employee $employee): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'employee_id')
            && DB::table($table)
                ->where('employee_id', $employee->id)
                ->exists();
    }
}
