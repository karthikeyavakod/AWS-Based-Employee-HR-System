<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Delete months that conflict with tests (4, 5, 6, 7, 8, 9, 10)
DB::table('payroll_runs')->whereIn('period_month', [4, 5, 6, 7, 8, 9, 10])->where('period_year', 2026)->delete();
echo "Conflicting payroll runs deleted.\n";
