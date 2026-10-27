<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\UpdateProfileRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $this->employeeFor($request);
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $attendances = $employee->attendances()
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->latest('attendance_date')
            ->limit(10)
            ->get();
        $attendanceCount = $employee->attendances()
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->count();
        $todayAttendance = $employee->attendances()
            ->whereDate('attendance_date', today())
            ->first();

        $leaveRequests = $employee->leaveRequests()
            ->latest('start_date')
            ->limit(10)
            ->get();

        $leaveBalances = collect();
        if (Schema::hasTable('leave_balances') && Schema::hasTable('leave_types')) {
            $leaveBalances = DB::table('leave_balances')
                ->leftJoin('leave_types', 'leave_types.id', '=', 'leave_balances.leave_type_id')
                ->where('leave_balances.employee_id', $employee->id)
                ->where('leave_balances.year', now()->year)
                ->select([
                    'leave_types.name as leave_type',
                    'leave_balances.allocated_days',
                    'leave_balances.used_days',
                    'leave_balances.remaining_days',
                ])
                ->orderBy('leave_types.name')
                ->get();
        }

        $payslips = collect();
        if (Schema::hasTable('payslips') && Schema::hasTable('payroll_runs')) {
            $payslips = DB::table('payslips')
                ->join('payroll_runs', 'payroll_runs.id', '=', 'payslips.payroll_run_id')
                ->where('payslips.employee_id', $employee->id)
                ->where('payslips.status', 'paid')
                ->select([
                    'payroll_runs.period_year',
                    'payroll_runs.period_month',
                    'payroll_runs.payment_date',
                    'payslips.net_salary',
                ])
                ->orderByDesc('payroll_runs.period_year')
                ->orderByDesc('payroll_runs.period_month')
                ->limit(12)
                ->get();
        }

        return view('employee.dashboard', compact(
            'employee',
            'attendances',
            'attendanceCount',
            'todayAttendance',
            'leaveRequests',
            'leaveBalances',
            'payslips',
        ));
    }

    public function editProfile(Request $request): View
    {
        $employee = $this->employeeFor($request);

        return view('employee.profile', compact('employee'));
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        $employee->update($request->validated());

        return redirect()
            ->route('employee.profile.edit')
            ->with('success', 'Your profile details were updated.');
    }

    public function checkIn(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        abort_unless($employee->status === 'active', 403, 'Inactive employees cannot record attendance.');

        $attendanceService->create([
            'employee_id' => $employee->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => 'present',
        ]);

        return redirect()->route('employee.dashboard')->with('success', 'You are checked in.');
    }

    public function checkOut(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        $employee = $this->employeeFor($request);
        abort_unless($employee->status === 'active', 403, 'Inactive employees cannot record attendance.');

        $attendance = DB::transaction(function () use ($employee, $attendanceService): ?Attendance {
            $attendance = Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', today())
                ->lockForUpdate()
                ->first();

            if ($attendance === null || $attendance->check_in_at === null || $attendance->check_out_at !== null) {
                return null;
            }

            return $attendanceService->update($attendance, ['check_out_at' => now()]);
        });

        if ($attendance === null) {
            return redirect()
                ->route('employee.dashboard')
                ->with('error', 'There is no open attendance record to check out from today.');
        }

        return redirect()->route('employee.dashboard')->with('success', 'You are checked out.');
    }

    public function announcements(): View
    {
        $employee = $this->employeeFor(request());

        $announcements = CompanyAnnouncement::query()
            ->published()
            ->with('creator')
            ->latest()
            ->get();

        return view('employee.announcements', compact('employee', 'announcements'));
    }

    private function employeeFor(Request $request): Employee
    {
        $user = $request->user();
        abort_unless(
            $user?->hasRole('employee') && ! $user->hasAnyRole(['super-admin', 'hr-manager']),
            403,
        );

        return Employee::query()
            ->with(['department', 'designation'])
            ->where('user_id', $user->id)
            ->firstOrFail();
    }
}
