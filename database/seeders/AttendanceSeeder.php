<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please seed employees first.');
            return;
        }

        $statuses = ['present', 'absent', 'late', 'half_day'];
        $weights = [70, 15, 10, 5];

        foreach ($employees as $employee) {
            $recordCount = 10 + ($employee->id % 6);

            foreach (range(0, $recordCount - 1) as $daysAgo) {
                $date = Carbon::today()->subDays($daysAgo);
                $status = $this->weightedRandom($statuses, $weights);

                $checkIn = null;
                $checkOut = null;
                $workMinutes = null;

                if ($status === 'present') {
                    $checkIn = $date->copy()->setTime(9, random_int(0, 30), 0);
                    $checkOut = $date->copy()->setTime(17, random_int(30, 59), 0);
                    $workMinutes = $checkIn->diffInMinutes($checkOut) - 60;
                } elseif ($status === 'late') {
                    $checkIn = $date->copy()->setTime(9, 0)->addMinutes(random_int(31, 90));
                    $checkOut = $date->copy()->setTime(17, random_int(30, 59), 0);
                    $workMinutes = $checkIn->diffInMinutes($checkOut) - 60;
                } elseif ($status === 'half_day') {
                    $checkIn = $date->copy()->setTime(9, random_int(0, 30), 0);
                    $checkOut = $date->copy()->setTime(13, random_int(0, 59), 0);
                    $workMinutes = $checkIn->diffInMinutes($checkOut) - 30;
                }

                Attendance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'attendance_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'check_in_at' => $checkIn,
                        'check_out_at' => $checkOut,
                        'status' => $status,
                        'work_minutes' => $workMinutes,
                        'remarks' => $status === 'absent' ? 'No show' : null,
                    ],
                );
            }
        }

        $this->command->info('Attendance records created for all employees.');
    }

    /**
     * Select a random value based on weights.
     */
    private function weightedRandom(array $values, array $weights): string
    {
        $total = array_sum($weights);
        $random = random_int(1, $total);
        $cumulative = 0;

        foreach ($values as $index => $value) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $value;
            }
        }

        return $values[0];
    }
}