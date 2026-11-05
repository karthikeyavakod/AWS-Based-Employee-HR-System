<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\CompanyAnnouncement;
use App\Models\Department;
use App\Models\DepartmentManager;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\User;
use App\Observers\AuditLogObserver;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        foreach ([
            User::class,
            Employee::class,
            Department::class,
            Designation::class,
            Attendance::class,
            AttendanceCorrection::class,
            LeaveRequest::class,
            Payroll::class,
            Role::class,
            CompanyAnnouncement::class,
            DepartmentManager::class,
        ] as $model) {
            $model::observe(AuditLogObserver::class);
        }

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                app(AuditLogService::class)->log(
                    'login',
                    'auth',
                    (int) $event->user->getKey(),
                    'User',
                    [],
                    [],
                    'User signed in',
                    $event->user,
                    $event->user->name,
                );
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User) {
                app(AuditLogService::class)->log(
                    'logout',
                    'auth',
                    (int) $event->user->getKey(),
                    'User',
                    [],
                    [],
                    'User signed out',
                    $event->user,
                    $event->user->name,
                );
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower($request->string('email')->toString()));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
