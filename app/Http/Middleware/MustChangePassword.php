<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->must_change_password) {
            $exceptRoutes = [
                route('employee.password.change'),
                route('employee.password.update'),
                route('logout'),
            ];

            if (! in_array($request->path(), $exceptRoutes, true)) {
                return redirect()
                    ->route('employee.password.change')
                    ->with('warning', 'You must change your temporary password before accessing the dashboard.');
            }
        }

        return $next($request);
    }
}