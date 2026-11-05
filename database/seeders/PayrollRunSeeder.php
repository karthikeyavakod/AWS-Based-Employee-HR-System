<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollRunSeeder extends Seeder
{
    /**
     * @var list<array<string, mixed>>
     */
    private array $payrollRuns = [
        [
            'title' => 'January 2026 Payroll',
            'period_year' => 2026,
            'period_month' => 1,
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'status' => 'paid',
            'payment_date' => '2026-02-05',
        ],
        [
            'title' => 'February 2026 Payroll',
            'period_year' => 2026,
            'period_month' => 2,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'status' => 'paid',
            'payment_date' => '2026-03-05',
        ],
        [
            'title' => 'March 2026 Payroll',
            'period_year' => 2026,
            'period_month' => 3,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'status' => 'paid',
            'payment_date' => '2026-04-05',
        ],
        [
            'title' => 'November 2026 Payroll',
            'period_year' => 2026,
            'period_month' => 11,
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'status' => 'draft',
            'payment_date' => null,
        ],
        [
            'title' => 'December 2026 Payroll',
            'period_year' => 2026,
            'period_month' => 12,
            'period_start' => '2026-12-01',
            'period_end' => '2026-12-31',
            'status' => 'draft',
            'payment_date' => null,
        ],
    ];

    public function run(): void
    {
        $payrollRuns = $this->payrollRuns;
        $currentMonth = today();

        if (! collect($payrollRuns)->contains(
            fn (array $payrollRun): bool => $payrollRun['period_year'] === (int) $currentMonth->format('Y')
                && $payrollRun['period_month'] === (int) $currentMonth->format('n')
        )) {
            $periodStart = $currentMonth->copy()->startOfMonth();
            $periodEnd = $currentMonth->copy()->endOfMonth();
            $payrollRuns[] = [
                'title' => $currentMonth->format('F Y').' Payroll',
                'period_year' => (int) $currentMonth->format('Y'),
                'period_month' => (int) $currentMonth->format('n'),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'status' => 'draft',
                'payment_date' => null,
            ];
        }

        foreach ($payrollRuns as $payrollRun) {
            DB::table('payroll_runs')->updateOrInsert(
                ['period_year' => $payrollRun['period_year'], 'period_month' => $payrollRun['period_month']],
                [
                    'title' => $payrollRun['title'],
                    'period_start' => $payrollRun['period_start'],
                    'period_end' => $payrollRun['period_end'],
                    'status' => $payrollRun['status'],
                    'payment_date' => $payrollRun['payment_date'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
