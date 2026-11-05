<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Audit Logs</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #20333b; font-size: 9px; }
        h1 { margin: 0 0 6px; font-size: 19px; }
        .meta { margin-bottom: 14px; color: #526873; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px; border: 1px solid #dce7eb; text-align: left; vertical-align: top; }
        th { background: #eaf6fa; }
        tr:nth-child(even) { background: #f7fafb; }
    </style>
</head>
<body>
    <h1>Smart HR Connect — Audit Logs</h1>
    <div class="meta">
        Date range: {{ $filters['date_from'] ?: 'Any' }} to {{ $filters['date_to'] ?: 'Any' }}<br>
        Generated: {{ $generatedAt->format('Y-m-d H:i:s T') }}<br>
        Matching records: {{ number_format($auditLogs->count()) }}
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>User</th><th>Role</th><th>Action</th><th>Module</th>
                <th>Subject</th><th>Description</th><th>IP address</th><th>Date &amp; Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($auditLogs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->user?->name ?? 'System' }}</td>
                    <td>{{ $log->user?->roles->pluck('name')->join(', ') ?: 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($log->action) }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($log->module) }}</td>
                    <td>{{ $log->subject_name ? $log->subject_name.' — ' : '' }}{{ $log->subject_type ?? 'N/A' }}#{{ $log->subject_id ?? 'N/A' }}</td>
                    <td>{{ $log->description ?? '' }}</td>
                    <td>{{ $log->ip_address ?? 'N/A' }}</td>
                    <td>{{ $log->created_at?->format('Y-m-d H:i:s T') }}</td>
                </tr>
            @empty
                <tr><td colspan="9">No audit activities match these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
