<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(string $action, string $module, ?int $subjectId = null, ?string $subjectType = null, array $oldValues = [], array $newValues = []): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'module' => $module,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}