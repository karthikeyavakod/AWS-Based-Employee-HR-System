<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Admin\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $relationships = $this->attendanceRelationships();

        $attendances = Attendance::query()
            ->with($relationships)
            ->latest('attendance_date')
            ->latest()
            ->paginate(15);

        return view('admin.attendance.index', compact('attendances'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->when(method_exists(Employee::class, 'user'), fn ($query) => $query->with('user'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.attendance.create', compact('employees'));
    }

    public function store(StoreAttendanceRequest $request, AttendanceService $attendanceService): RedirectResponse
    {
        $attendanceService->create($request->validated());

        return redirect()
            ->route('admin.attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }

    public function edit(Attendance $attendance): View
    {
        $attendance->load($this->attendanceRelationships());

        $employees = Employee::query()
            ->when(method_exists(Employee::class, 'user'), fn ($query) => $query->with('user'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.attendance.edit', compact('attendance', 'employees'));
    }

    public function update(
        UpdateAttendanceRequest $request,
        Attendance $attendance,
        AttendanceService $attendanceService
    ): RedirectResponse {
        $attendanceService->update($attendance, $request->validated());

        return redirect()
            ->route('admin.attendance.edit', $attendance)
            ->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(Attendance $attendance, AttendanceService $attendanceService): RedirectResponse
    {
        try {
            $attendanceService->delete($attendance);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.attendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * @return list<string>
     */
    private function attendanceRelationships(): array
    {
        if (! method_exists(Attendance::class, 'employee')) {
            return [];
        }

        return method_exists(Employee::class, 'user')
            ? ['employee.user']
            : ['employee'];
    }
}
