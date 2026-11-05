<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogService
{
    private const SENSITIVE_KEY_PATTERN = '/password|token|secret|credential|api.?key|private.?key|authorization|cookie/i';

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(
        string $action,
        string $module,
        ?int $subjectId = null,
        ?string $subjectType = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null,
        ?User $actor = null,
        ?string $subjectName = null,
    ): void {
        $actor ??= Auth::user();

        AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'module' => $module,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'description' => $description ?? sprintf('%s %s', Str::headline($module), Str::headline($action)),
            'subject_name' => $subjectName,
            'ip_address' => request()->ip(),
            'user_agent' => $this->clientDetails(request()->userAgent()),
        ]);
    }

    private function clientDetails(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return null;
        }

        $browser = match (true) {
            preg_match('/Edg\/([\d.]+)/i', $userAgent, $matches) === 1 => 'Edge '.$matches[1],
            preg_match('/Firefox\/([\d.]+)/i', $userAgent, $matches) === 1 => 'Firefox '.$matches[1],
            preg_match('/Chrome\/([\d.]+)/i', $userAgent, $matches) === 1 => 'Chrome '.$matches[1],
            preg_match('/Version\/([\d.]+).*Safari/i', $userAgent, $matches) === 1 => 'Safari '.$matches[1],
            default => 'Unknown browser',
        };
        $operatingSystem = match (true) {
            preg_match('/Windows NT/i', $userAgent) === 1 => 'Windows',
            preg_match('/iPhone|iPad|iPod/i', $userAgent) === 1 => 'iOS',
            preg_match('/Android/i', $userAgent) === 1 => 'Android',
            preg_match('/Mac OS X/i', $userAgent) === 1 => 'macOS',
            preg_match('/Linux/i', $userAgent) === 1 => 'Linux',
            default => 'Unknown OS',
        };
        $device = preg_match('/iPhone|iPad|iPod|Android|Mobile|Tablet/i', $userAgent) === 1
            ? 'Mobile/tablet'
            : 'Desktop';

        return "{$browser} / {$operatingSystem} / {$device}";
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sanitize(array $values): array
    {
        $safeValues = [];

        foreach ($values as $key => $value) {
            if (preg_match(self::SENSITIVE_KEY_PATTERN, (string) $key) === 1) {
                continue;
            }

            $safeValues[$key] = is_array($value)
                ? $this->sanitize($value)
                : (is_scalar($value) || $value === null ? $value : (string) $value);
        }

        return $safeValues;
    }
}
