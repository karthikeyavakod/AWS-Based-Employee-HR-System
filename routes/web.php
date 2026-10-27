<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:role.view')
            ->name('roles.index');

        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware('permission:role.create')
            ->name('roles.create');

        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:role.create')
            ->name('roles.store');

        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('permission:role.update')
            ->name('roles.edit');

        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:role.update')
            ->name('roles.update');

        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:role.delete')
            ->name('roles.destroy');

        Route::get('/roles/{role}/permissions', [RoleController::class, 'editPermissions'])
            ->middleware('permission:permission.assign')
            ->name('roles.permissions.edit');

        Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->middleware('permission:permission.assign')
            ->name('roles.permissions.update');

        Route::get('/permissions', [PermissionController::class, 'index'])
            ->middleware('permission:permission.view')
            ->name('permissions.index');

        Route::get('/departments', [DepartmentController::class, 'index'])
            ->middleware('permission:department.view')
            ->name('departments.index');

        Route::get('/departments/create', [DepartmentController::class, 'create'])
            ->middleware('permission:department.create')
            ->name('departments.create');

        Route::post('/departments', [DepartmentController::class, 'store'])
            ->middleware('permission:department.create')
            ->name('departments.store');

        Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])
            ->middleware('permission:department.update')
            ->name('departments.edit');

        Route::put('/departments/{department}', [DepartmentController::class, 'update'])
            ->middleware('permission:department.update')
            ->name('departments.update');

        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])
            ->middleware('permission:department.delete')
            ->name('departments.destroy');

        Route::get('/designations', [DesignationController::class, 'index'])
            ->middleware('permission:designation.view')
            ->name('designations.index');

        Route::get('/designations/create', [DesignationController::class, 'create'])
            ->middleware('permission:designation.create')
            ->name('designations.create');

        Route::post('/designations', [DesignationController::class, 'store'])
            ->middleware('permission:designation.create')
            ->name('designations.store');

        Route::get('/designations/{designation}/edit', [DesignationController::class, 'edit'])
            ->middleware('permission:designation.update')
            ->name('designations.edit');

        Route::put('/designations/{designation}', [DesignationController::class, 'update'])
            ->middleware('permission:designation.update')
            ->name('designations.update');

        Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])
            ->middleware('permission:designation.delete')
            ->name('designations.destroy');

        Route::get('/employees', [EmployeeController::class, 'index'])
            ->middleware('permission:employee.view')
            ->name('employees.index');

        Route::get('/employees/create', [EmployeeController::class, 'create'])
            ->middleware('permission:employee.create')
            ->name('employees.create');

        Route::post('/employees', [EmployeeController::class, 'store'])
            ->middleware('permission:employee.create')
            ->name('employees.store');

        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])
            ->middleware('permission:employee.update')
            ->name('employees.edit');

        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])
            ->middleware('permission:employee.update')
            ->name('employees.update');

        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
            ->middleware('permission:employee.delete')
            ->name('employees.destroy');

        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->middleware('permission:attendance.view')
            ->name('attendance.index');

        Route::get('/attendance/create', [AttendanceController::class, 'create'])
            ->middleware('permission:attendance.create')
            ->name('attendance.create');

        Route::post('/attendance', [AttendanceController::class, 'store'])
            ->middleware('permission:attendance.create')
            ->name('attendance.store');

        Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])
            ->middleware('permission:attendance.update')
            ->name('attendance.edit');

        Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])
            ->middleware('permission:attendance.update')
            ->name('attendance.update');

        Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])
            ->middleware('permission:attendance.delete')
            ->name('attendance.destroy');

        Route::get('/leaves', [LeaveRequestController::class, 'index'])
            ->middleware('permission:leave.view')
            ->name('leaves.index');

        Route::get('/leaves/create', [LeaveRequestController::class, 'create'])
            ->middleware('permission:leave.create')
            ->name('leaves.create');

        Route::post('/leaves', [LeaveRequestController::class, 'store'])
            ->middleware('permission:leave.create')
            ->name('leaves.store');

        Route::get('/leaves/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])
            ->middleware('permission:leave.view')
            ->name('leaves.edit');

        Route::put('/leaves/{leaveRequest}', [LeaveRequestController::class, 'update'])
            ->middleware('permission:leave.view')
            ->name('leaves.update');

        Route::delete('/leaves/{leaveRequest}', [LeaveRequestController::class, 'destroy'])
            ->middleware('permission:leave.delete')
            ->name('leaves.destroy');

        Route::patch('/leaves/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
            ->middleware('permission:leave.approve')
            ->name('leaves.approve');

        Route::patch('/leaves/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
            ->middleware('permission:leave.reject')
            ->name('leaves.reject');

        Route::get('/payrolls', [PayrollController::class, 'index'])
            ->middleware('permission:payroll.view')
            ->name('payrolls.index');

        Route::get('/payrolls/create', [PayrollController::class, 'create'])
            ->middleware('permission:payroll.create')
            ->name('payrolls.create');

        Route::post('/payrolls', [PayrollController::class, 'store'])
            ->middleware('permission:payroll.create')
            ->name('payrolls.store');

        Route::get('/payrolls/{payroll}/edit', [PayrollController::class, 'edit'])
            ->middleware('permission:payroll.update')
            ->name('payrolls.edit');

        Route::put('/payrolls/{payroll}', [PayrollController::class, 'update'])
            ->middleware('permission:payroll.update')
            ->name('payrolls.update');

        Route::delete('/payrolls/{payroll}', [PayrollController::class, 'destroy'])
            ->middleware('permission:payroll.delete')
            ->name('payrolls.destroy');

        Route::get('/reports', [ReportController::class, 'index'])
            ->middleware('permission:report.view')
            ->name('reports.index');

        Route::get('/reports/employees', [ReportController::class, 'employees'])
            ->middleware('permission:report.view')
            ->name('reports.employees');

        Route::get('/reports/attendance', [ReportController::class, 'attendance'])
            ->middleware('permission:report.view')
            ->name('reports.attendance');

        Route::get('/reports/leaves', [ReportController::class, 'leaves'])
            ->middleware('permission:report.view')
            ->name('reports.leaves');

        Route::get('/reports/payrolls', [ReportController::class, 'payrolls'])
            ->middleware('permission:report.view')
            ->name('reports.payrolls');
    });
