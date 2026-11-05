<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    private const PAGE_SIZES = [10, 25, 50, 100];

    private const ACTIONS = ['created', 'updated', 'deleted', 'approved', 'rejected', 'login', 'logout'];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $baseQuery = $this->filteredQuery($filters);
        $auditLogs = (clone $baseQuery)
            ->with('user.roles')
            ->latest('audit_logs.created_at')
            ->latest('audit_logs.id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        $allLogs = AuditLog::query();
        $summary = [
            'total' => (clone $allLogs)->count(),
            'today' => (clone $allLogs)->whereDate('created_at', today())->count(),
            'logins' => (clone $allLogs)->where('action', 'login')->count(),
            'critical' => (clone $allLogs)->whereIn('action', ['deleted', 'rejected'])->count(),
        ];
        $modules = AuditLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $users = DB::table('users')
            ->join('audit_logs', 'audit_logs.user_id', '=', 'users.id')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        return view('admin.audit-logs.index', compact('auditLogs', 'filters', 'summary', 'modules', 'users'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $logs = $this->filteredQuery($filters)
            ->with('user.roles')
            ->latest('audit_logs.created_at')
            ->latest('audit_logs.id')
            ->lazy(500);
        $generatedAt = now();
        $filename = 'audit-logs-'.$generatedAt->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($logs, $filters, $generatedAt): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new \RuntimeException('Unable to open the audit CSV output stream.');
            }

            fputcsv($output, ['Smart HR Connect - Audit Logs']);
            fputcsv($output, ['Date range', $filters['date_from'] ?: 'Any', $filters['date_to'] ?: 'Any']);
            fputcsv($output, ['Generated at', $generatedAt->format('Y-m-d H:i:s T')]);
            fputcsv($output, []);
            fputcsv($output, ['ID', 'User', 'Role', 'Action', 'Module', 'Subject', 'Description', 'IP Address', 'Date & Time']);

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->id,
                    $this->csvValue($log->user?->name ?? 'System'),
                    $this->csvValue($log->user?->roles->pluck('name')->join(', ') ?: 'N/A'),
                    $this->csvValue($log->action),
                    $this->csvValue($log->module),
                    $this->csvValue(trim(($log->subject_name ? $log->subject_name.' — ' : '').($log->subject_type ?? 'N/A').'#'.($log->subject_id ?? 'N/A'))),
                    $this->csvValue($log->description),
                    $this->csvValue($log->ip_address),
                    $log->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $auditLogs = $this->filteredQuery($filters)
            ->with('user.roles')
            ->latest('audit_logs.created_at')
            ->latest('audit_logs.id')
            ->get();
        $generatedAt = now();

        return Pdf::loadView('admin.audit-logs.export-pdf', compact('auditLogs', 'filters', 'generatedAt'))
            ->setPaper('a4', 'landscape')
            ->download('audit-logs-'.$generatedAt->format('Ymd-His').'.pdf');
    }

    /**
     * @return array{q: string, module: ?string, user_id: ?int, action: ?string, date_from: ?string, date_to: ?string, per_page: int}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'module' => ['nullable', 'string', Rule::in(AuditLog::query()->distinct()->pluck('module')->all())],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', Rule::in(self::ACTIONS)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', Rule::in(self::PAGE_SIZES)],
        ]);

        return [
            'q' => trim($validated['q'] ?? ''),
            'module' => $validated['module'] ?? null,
            'user_id' => isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            'action' => $validated['action'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'per_page' => (int) ($validated['per_page'] ?? 25),
        ];
    }

    /**
     * @param  array{q: string, module: ?string, user_id: ?int, action: ?string, date_from: ?string, date_to: ?string, per_page: int}  $filters
     * @return Builder<AuditLog>
     */
    private function filteredQuery(array $filters): Builder
    {
        return AuditLog::query()
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $search = '%'.$filters['q'].'%';
                $query->where(function (Builder $query) use ($search, $filters): void {
                    $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $search))
                        ->orWhere('action', 'like', $search)
                        ->orWhere('module', 'like', $search)
                        ->orWhere('subject_type', 'like', $search)
                        ->orWhere('subject_name', 'like', $search)
                        ->orWhere('description', 'like', $search);

                    if (ctype_digit($filters['q'])) {
                        $query->orWhere('subject_id', (int) $filters['q']);
                    }
                });
            })
            ->when($filters['module'] !== null, fn (Builder $query) => $query->where('module', $filters['module']))
            ->when($filters['user_id'] !== null, fn (Builder $query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['action'] !== null, fn (Builder $query) => $query->where('action', $filters['action']))
            ->when($filters['date_from'] !== null, fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== null, fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']));
    }

    private function csvValue(?string $value): ?string
    {
        return $value !== null && preg_match('/^[\t\r=+\-@]/', $value) === 1
            ? "'".$value
            : $value;
    }
}
