<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceCorrectionController;
use App\Http\Controllers\Admin\CompanyAnnouncementController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DepartmentManagerController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeePortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login');

    Route::get('/forgot-password', [NewPasswordController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [NewPasswordController::class, 'email'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/password/reset/{token}', [NewPasswordController::class, 'edit'])
        ->name('password.reset');

    Route::post('/password/reset', [NewPasswordController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware(['auth', 'must.change.password'])->prefix('my')->name('employee.')->group(function () {
    Route::get('/', [EmployeePortalController::class, 'index'])->name('dashboard');
    Route::get('/profile', [EmployeePortalController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [EmployeePortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/password/change', [Employee\PasswordChangeController::class, 'show'])->name('password.change');
    Route::put('/password/change', [Employee\PasswordChangeController::class, 'update'])->name('password.update');
    Route::post('/attendance/check-in', [EmployeePortalController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [EmployeePortalController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/announcements', [EmployeePortalController::class, 'announcements'])->name('announcements');
});

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

        Route::post('/employees/{employee}/invite', [EmployeeController::class, 'invite'])
            ->middleware('permission:user.create')
            ->name('employees.invite');

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

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->middleware('permission:audit.log.view')
            ->name('audit-logs.index');

        Route::get('/department-managers', [DepartmentManagerController::class, 'index'])
            ->middleware('permission:user.view')
            ->name('department-managers.index');
        Route::get('/department-managers/create', [DepartmentManagerController::class, 'create'])
            ->middleware('permission:user.create')
            ->name('department-managers.create');
        Route::post('/department-managers', [DepartmentManagerController::class, 'store'])
            ->middleware('permission:user.create')
            ->name('department-managers.store');
        Route::delete('/department-managers/{departmentManager}', [DepartmentManagerController::class, 'destroy'])
            ->middleware('permission:user.delete')
            ->name('department-managers.destroy');

        Route::get('/announcements', [CompanyAnnouncementController::class, 'index'])
            ->middleware('permission:announcement.view')
            ->name('announcements.index');
        Route::get('/announcements/create', [CompanyAnnouncementController::class, 'create'])
            ->middleware('permission:announcement.create')
            ->name('announcements.create');
        Route::post('/announcements', [CompanyAnnouncementController::class, 'store'])
            ->middleware('permission:announcement.create')
            ->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [CompanyAnnouncementController::class, 'edit'])
            ->middleware('permission:announcement.update')
            ->name('announcements.edit');
        Route::put('/announcements/{announcement}', [CompanyAnnouncementController::class, 'update'])
            ->middleware('permission:announcement.update')
            ->name('announcements.update');
        Route::delete('/announcements/{announcement}', [CompanyAnnouncementController::class, 'destroy'])
            ->middleware('permission:announcement.delete')
            ->name('announcements.destroy');

        Route::get('/attendance-corrections', [AttendanceCorrectionController::class, 'index'])
            ->middleware('permission:attendance.view')
            ->name('attendance-corrections.index');
        Route::get('/attendance-corrections/create', [AttendanceCorrectionController::class, 'create'])
            ->middleware('permission:attendance.correction')
            ->name('attendance-corrections.create');
        Route::post('/attendance-corrections', [AttendanceCorrectionController::class, 'store'])
            ->middleware('permission:attendance.correction')
            ->name('attendance-corrections.store');
        Route::get('/attendance-corrections/{attendanceCorrection}/review', [AttendanceCorrectionController::class, 'review'])
            ->middleware('permission:attendance.view')
            ->name('attendance-corrections.review');
        Route::put('/attendance-corrections/{attendanceCorrection}', [AttendanceCorrectionController::class, 'update'])
            ->middleware('permission:attendance.view')
            ->name('attendance-corrections.update');
    });
