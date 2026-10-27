<?php

namespace App\Services;

use App\Models\Designation;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DesignationService
{
    public function create(array $data): Designation
    {
        return DB::transaction(function () use ($data): Designation {
            return Designation::create($this->prepareData($data));
        });
    }

    public function update(Designation $designation, array $data): Designation
    {
        return DB::transaction(function () use ($designation, $data): Designation {
            $designation->update($this->prepareData($data, $designation));

            return $designation->refresh();
        });
    }

    public function delete(Designation $designation): bool
    {
        if ($this->hasRelatedEmployees($designation)) {
            throw new DomainException('This designation has employees and cannot be deleted.');
        }

        return DB::transaction(fn (): bool => (bool) $designation->delete());
    }

    private function prepareData(array $data, ?Designation $designation = null): array
    {
        $payload = Arr::only($data, ['department_id', 'name', 'code', 'slug', 'description', 'status']);

        foreach (array_keys($payload) as $column) {
            if (! Schema::hasColumn('designations', $column)) {
                unset($payload[$column]);
            }
        }

        $this->fillGeneratedIdentifier($payload, 'code', $designation);
        $this->fillGeneratedIdentifier($payload, 'slug', $designation);

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function fillGeneratedIdentifier(array &$payload, string $column, ?Designation $designation = null): void
    {
        if (! Schema::hasColumn('designations', $column)) {
            return;
        }

        $shouldGenerate = $designation === null
            ? blank($payload[$column] ?? null)
            : array_key_exists($column, $payload) && blank($payload[$column]);

        if (! $shouldGenerate) {
            return;
        }

        $payload[$column] = $this->uniqueIdentifier($payload['name'], $column, $designation);
    }

    private function uniqueIdentifier(string $name, string $column, ?Designation $designation = null): string
    {
        $baseIdentifier = $column === 'code'
            ? Str::upper(Str::slug($name, '-'))
            : Str::slug($name);

        if ($baseIdentifier === '') {
            $baseIdentifier = $column === 'code' ? 'DESIGNATION' : 'designation';
        }

        $identifier = $baseIdentifier;
        $counter = 2;

        while (
            Designation::withTrashed()
                ->where($column, $identifier)
                ->when($designation !== null, fn ($query) => $query->whereKeyNot($designation->getKey()))
                ->exists()
        ) {
            $identifier = "{$baseIdentifier}-{$counter}";
            $counter++;
        }

        return $identifier;
    }

    private function hasRelatedEmployees(Designation $designation): bool
    {
        return Schema::hasTable('employees')
            && Schema::hasColumn('employees', 'designation_id')
            && DB::table('employees')
                ->where('designation_id', $designation->id)
                ->exists();
    }
}
