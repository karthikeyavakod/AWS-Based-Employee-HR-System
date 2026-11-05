<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollSeeder extends Seeder
{
    /**
     * @var list<array{gross_salary: int, total_deductions: int}>
     */
    private array $samplePayrolls = [
        ['gross_salary' => 52000, 'total_deductions' => 5200],
        ['gross_salary' => 61000, 'total_deductions' => 6100],
        ['gross_salary' => 47500, 'total_deductions' => 4750],
        ['gross_salary' => 68000, 'total_deductions' => 6800],
        ['gross_salary' => 55500, 'total_deductions' => 5550],
        ['gross_salary' => 73000, 'total_deductions' => 7300],
    ];

    public function run(): void
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->limit(count($this->samplePayrolls))
            ->get(['id']);

        if ($employees->isEmpty()) {
            $this->command?->warn('No active employees found. Payroll sample records were not created.');

            return;
        }

        $currentMonth = today();
        $payrollRun = DB::table('payroll_runs')
            ->where('period_year', (int) $currentMonth->format('Y'))
            ->where('period_month', (int) $currentMonth->format('n'))
            ->first(['id']);

        if ($payrollRun === null) {
            $this->command?->warn('No current-month payroll run found. Payroll sample records were not created.');

            return;
        }

        foreach ($employees as $index => $employee) {
            $sample = $this->samplePayrolls[$index];

            Payroll::query()->firstOrCreate(
                [
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'gross_salary' => $sample['gross_salary'],
                    'total_deductions' => $sample['total_deductions'],
                    'net_salary' => $sample['gross_salary'] - $sample['total_deductions'],
                    'status' => 'paid',
                ],
            );
        }

        $this->command?->info(sprintf(
            'Payroll sample records are available for %d employees.',
            $employees->count(),
        ));
    }
}
