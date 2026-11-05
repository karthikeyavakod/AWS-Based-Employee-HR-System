<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $leaveTypes = [
        [
            'name' => 'Annual Leave',
            'code' => 'annual-leave',
            'default_days' => 12,
            'is_paid' => true,
            'status' => 'active',
        ],
        [
            'name' => 'Sick Leave',
            'code' => 'sick-leave',
            'default_days' => 10,
            'is_paid' => true,
            'status' => 'active',
        ],
        [
            'name' => 'Casual Leave',
            'code' => 'casual-leave',
            'default_days' => 8,
            'is_paid' => true,
            'status' => 'active',
        ],
        [
            'name' => 'Maternity Leave',
            'code' => 'maternity-leave',
            'default_days' => 90,
            'is_paid' => true,
            'status' => 'active',
        ],
        [
            'name' => 'Paternity Leave',
            'code' => 'paternity-leave',
            'default_days' => 5,
            'is_paid' => true,
            'status' => 'active',
        ],
        [
            'name' => 'Unpaid Leave',
            'code' => 'unpaid-leave',
            'default_days' => 0,
            'is_paid' => false,
            'status' => 'active',
        ],
    ];

    public function run(): void
    {
        foreach ($this->leaveTypes as $leaveType) {
            DB::table('leave_types')->updateOrInsert(
                ['code' => $leaveType['code']],
                [
                    'name' => $leaveType['name'],
                    'default_days' => $leaveType['default_days'],
                    'is_paid' => $leaveType['is_paid'],
                    'status' => $leaveType['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
