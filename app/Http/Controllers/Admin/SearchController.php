<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(): View
    {
        $query = request()->query('q', '');

        $employees = collect();

        if ($query !== '') {
            $employees = Employee::query()
                ->with(['user', 'department', 'designation'])
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('employee_code', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhereHas('user', function ($userQuery) use ($query) {
                            $userQuery->where('email', 'like', "%{$query}%");
                        });
                })
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('admin.search.results', compact('query', 'employees'));
    }
}