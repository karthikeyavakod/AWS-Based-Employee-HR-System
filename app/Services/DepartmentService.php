<?php

namespace App\Services;

use App\Models\Department;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DepartmentService
{
    public function create(array $data): Department
    {
        return DB::transaction(function () use ($data): Department {
            return Department::create($this->prepareData($data));
        });
    }

    public function update(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data): Department {
            $department->update($this->prepareData($data, $department));

            return $department->refresh();
        });
    }

    public function delete(Department $department): bool
    {
        if ($this->hasRelatedRecords('employees', $department)) {
            throw new DomainException('This department has employees and cannot be deleted.');
        }

        if ($this->hasRelatedRecords('designations', $department)) {
            throw new DomainException('This department has designations and cannot be deleted.');
        }

        return DB::transaction(fn (): bool => (bool) $department->delete());
    }

    private function prepareData(array $data, ?Department $department = null): array
    {
        $payload = Arr::only($data, ['name', 'code', 'slug', 'description', 'status']);

        foreach (array_keys($payload) as $column) {
            if (! Schema::hasColumn('departments', $column)) {
                unset($payload[$column]);
            }
        }

        $this->fillGeneratedIdentifier($payload, 'code', $department);
        $this->fillGeneratedIdentifier($payload, 'slug', $department);

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function fillGeneratedIdentifier(array &$payload, string $column, ?Department $department = null): void
    {
        if (! Schema::hasColumn('departments', $column)) {
            return;
        }

        $shouldGenerate = $department === null
            ? blank($payload[$column] ?? null)
            : array_key_exists($column, $payload) && blank($payload[$column]);

        if (! $shouldGenerate) {
            return;
        }

        $payload[$column] = $this->uniqueIdentifier($payload['name'], $column, $department);
    }

    private function uniqueIdentifier(string $name, string $column, ?Department $department = null): string
    {
        $baseIdentifier = $column === 'code'
            ? Str::upper(Str::slug($name, '-'))
            : Str::slug($name);

        if ($baseIdentifier === '') {
            $baseIdentifier = $column === 'code' ? 'DEPARTMENT' : 'department';
        }

        $identifier = $baseIdentifier;
        $counter = 2;

        while (
            Department::withTrashed()
                ->where($column, $identifier)
                ->when($department !== null, fn ($query) => $query->whereKeyNot($department->getKey()))
                ->exists()
        ) {
            $identifier = "{$baseIdentifier}-{$counter}";
            $counter++;
        }

        return $identifier;
    }

    private function hasRelatedRecords(string $table, Department $department): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'department_id')
            && DB::table($table)
                ->where('department_id', $department->id)
                ->exists();
    }
}
