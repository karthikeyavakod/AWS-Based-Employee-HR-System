<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Leave\StoreLeaveRequestRequest;
use App\Http\Requests\Admin\Leave\UpdateLeaveRequestRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $leaveRequests = LeaveRequest::query()
            ->with($this->leaveRequestRelationships())
            ->latest('start_date')
            ->latest()
            ->paginate(15);

        $leaveTypesById = $this->leaveTypes()->keyBy('id');

        return view('admin.leaves.index', compact('leaveRequests', 'leaveTypesById'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->when(method_exists(Employee::class, 'user'), fn ($query) => $query->with('user'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leaveTypes = $this->leaveTypes();

        return view('admin.leaves.create', compact('employees', 'leaveTypes'));
    }

    public function store(
        StoreLeaveRequestRequest $request,
        LeaveRequestService $leaveRequestService
    ): RedirectResponse {
        $leaveRequestService->create($request->validated());

        return redirect()
            ->route('admin.leaves.index')
            ->with('success', 'Leave request created successfully.');
    }

    public function edit(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load($this->leaveRequestRelationships());

        $employees = Employee::query()
            ->when(method_exists(Employee::class, 'user'), fn ($query) => $query->with('user'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $leaveTypes = $this->leaveTypes();

        return view('admin.leaves.edit', compact('leaveRequest', 'employees', 'leaveTypes'));
    }

    public function update(
        UpdateLeaveRequestRequest $request,
        LeaveRequest $leaveRequest,
        LeaveRequestService $leaveRequestService
    ): RedirectResponse {
        $leaveRequestService->update($leaveRequest, $request->validated());

        return redirect()
            ->route('admin.leaves.edit', $leaveRequest)
            ->with('success', 'Leave request updated successfully.');
    }

    public function destroy(
        LeaveRequest $leaveRequest,
        LeaveRequestService $leaveRequestService
    ): RedirectResponse {
        try {
            $leaveRequestService->delete($leaveRequest);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.leaves.index')
            ->with('success', 'Leave request deleted successfully.');
    }

    public function approve(
        LeaveRequest $leaveRequest,
        LeaveRequestService $leaveRequestService
    ): RedirectResponse {
        try {
            $leaveRequestService->approve($leaveRequest);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leave request approved successfully.');
    }

    public function reject(
        LeaveRequest $leaveRequest,
        LeaveRequestService $leaveRequestService
    ): RedirectResponse {
        try {
            $leaveRequestService->reject($leaveRequest);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leave request rejected successfully.');
    }

    /**
     * @return list<string>
     */
    private function leaveRequestRelationships(): array
    {
        $relationships = [];

        if (method_exists(LeaveRequest::class, 'employee')) {
            $relationships[] = method_exists(Employee::class, 'user')
                ? 'employee.user'
                : 'employee';
        }

        if (method_exists(LeaveRequest::class, 'approver')) {
            $relationships[] = 'approver';
        }

        return $relationships;
    }

    private function leaveTypes(): Collection
    {
        if (! Schema::hasTable('leave_types')) {
            return collect();
        }

        $columns = collect(['id', 'name', 'code', 'status'])
            ->filter(fn (string $column): bool => Schema::hasColumn('leave_types', $column))
            ->values()
            ->all();

        $query = DB::table('leave_types')->select($columns);

        if (Schema::hasColumn('leave_types', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query
            ->orderBy(Schema::hasColumn('leave_types', 'name') ? 'name' : 'id')
            ->get();
    }
}
