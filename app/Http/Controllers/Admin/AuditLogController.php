<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $auditLogs = AuditLog::query()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('admin.audit-logs.index', compact('auditLogs'));
    }
}