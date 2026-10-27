<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceCorrectionController extends Controller
{
    public function index(): View
    {
        $corrections = AttendanceCorrection::query()
            ->with(['employee.user'])
            ->latest()
            ->paginate(15);

        return view('admin.attendance-corrections.index', compact('corrections'));
    }

    public function create(): View
    {
        $employees = Employee::query()->with('user')->orderBy('first_name')->get();
        return view('admin.attendance-corrections.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'requested_check_in_at' => ['nullable', 'date'],
            'requested_check_out_at' => ['nullable', 'date', 'after:requested_check_in_at'],
            'requested_status' => ['nullable', 'string', 'in:present,absent,late,half_day'],
            'reason' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            AttendanceCorrection::create(array_merge($validated, [
                'status' => 'pending',
            ]));
        });

        return redirect()->route('admin.attendance-corrections.index')->with('success', 'Attendance correction request submitted successfully.');
    }

    public function review(AttendanceCorrection $correction): View
    {
        $correction->load('employee.user');
        return view('admin.attendance-corrections.review', compact('correction'));
    }

    public function update(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected'],
            'reviewer_comments' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($correction, $validated) {
            $correction->update([
                'status' => $validated['status'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'reviewer_comments' => $validated['reviewer_comments'] ?? null,
            ]);
        });

        return redirect()->route('admin.attendance-corrections.index')->with('success', 'Attendance correction ' . $validated['status'] . ' successfully.');
    }
}