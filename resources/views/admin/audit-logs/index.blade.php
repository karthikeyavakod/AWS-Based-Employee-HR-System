@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    @php
        $actionStyles = [
            'created' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            'login' => 'purple',
            'logout' => 'secondary',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Audit Logs</h1>
            <p class="text-body-secondary mb-0">Monitor system activity and administrative changes.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.audit-logs.index', request()->query()) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </a>
            <a href="{{ route('admin.audit-logs.export.csv', request()->query()) }}" class="btn btn-outline-primary">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </a>
            <a href="{{ route('admin.audit-logs.export.pdf', request()->query()) }}" class="btn btn-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Total Activities', 'value' => $summary['total'], 'icon' => 'bi-activity', 'tone' => 'teal'],
            ['label' => "Today's Activities", 'value' => $summary['today'], 'icon' => 'bi-calendar2-check', 'tone' => 'success'],
            ['label' => 'Login Activities', 'value' => $summary['logins'], 'icon' => 'bi-box-arrow-in-right', 'tone' => 'slate'],
            ['label' => 'Critical Activities', 'value' => $summary['critical'], 'icon' => 'bi-shield-exclamation', 'tone' => 'danger'],
        ] as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-stat-card h-100 p-3 d-flex align-items-center gap-3">
                    <span class="stat-icon stat-{{ $card['tone'] }}">
                        <i class="bi {{ $card['icon'] }} fs-4"></i>
                    </span>
                    <div>
                        <div class="small text-body-secondary">{{ $card['label'] }}</div>
                        <div class="h3 fw-bold mb-0">{{ number_format($card['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="audit-search" class="form-label">Search activity</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input id="audit-search" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="User, action, subject...">
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label for="audit-module" class="form-label">Module</label>
                        <select id="audit-module" name="module" class="form-select">
                            <option value="">All modules</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ \Illuminate\Support\Str::headline($module) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label for="audit-user" class="form-label">User</label>
                        <select id="audit-user" name="user_id" class="form-select">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($filters['user_id'] === $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label for="audit-action" class="form-label">Action</label>
                        <select id="audit-action" name="action" class="form-select">
                            <option value="">All actions</option>
                            @foreach (['created', 'updated', 'deleted', 'approved', 'rejected', 'login', 'logout'] as $action)
                                <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ \Illuminate\Support\Str::headline($action) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-1">
                        <label for="audit-date-from" class="form-label">From</label>
                        <input id="audit-date-from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                    </div>
                    <div class="col-6 col-md-3 col-xl-1">
                        <label for="audit-date-to" class="form-label">To</label>
                        <input id="audit-date-to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                    </div>
                    <div class="col-6 col-md-3 col-xl-1">
                        <label for="audit-page-size" class="form-label">Rows</label>
                        <select id="audit-page-size" name="per_page" class="form-select">
                            @foreach ([10, 25, 50, 100] as $pageSize)
                                <option value="{{ $pageSize }}" @selected($filters['per_page'] === $pageSize)>{{ $pageSize }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h2 class="h5 mb-0">Activity History</h2>
                <span class="small text-body-secondary">{{ number_format($auditLogs->total()) }} matching records</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">User</th>
                            <th scope="col">Action</th>
                            <th scope="col">Module</th>
                            <th scope="col">Subject</th>
                            <th scope="col">IP Address</th>
                            <th scope="col">Date &amp; Time</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $log)
                            @php
                                $actionLabel = \Illuminate\Support\Str::headline($log->action);
                                $detailPayload = [
                                    'user' => $log->user?->name ?? 'System',
                                    'roles' => $log->user?->roles->pluck('name')->join(', ') ?: 'N/A',
                                    'subjectName' => $log->subject_name ?: (($log->subject_type === 'User') ? ($log->user?->name ?? 'N/A') : 'N/A'),
                                    'action' => $actionLabel,
                                    'module' => \Illuminate\Support\Str::headline($log->module),
                                    'subject' => trim(($log->subject_name ? $log->subject_name.' — ' : '').($log->subject_type ?? 'N/A').' #'.($log->subject_id ?? 'N/A')),
                                    'description' => $log->description ?: $actionLabel.' '.\Illuminate\Support\Str::headline($log->module),
                                    'ip' => $log->ip_address ?: 'N/A',
                                    'agent' => $log->user_agent ?: 'Not available',
                                    'created' => $log->created_at?->format('l, F j, Y \a\t H:i:s T') ?? 'N/A',
                                    'old' => $log->old_values ?? [],
                                    'new' => $log->new_values ?? [],
                                ];
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $log->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                    <div class="small text-body-secondary">{{ $log->user?->roles->pluck('name')->join(', ') ?: 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge audit-action-badge audit-action-{{ $actionStyles[$log->action] ?? 'secondary' }}">
                                        {{ $actionLabel }}
                                    </span>
                                </td>
                                <td class="text-body-secondary small">{{ \Illuminate\Support\Str::headline($log->module) }}</td>
                                <td class="text-body-secondary small">
                                    <div class="fw-semibold text-body">{{ $log->subject_name ?? 'N/A' }}</div>
                                    <div>{{ $log->subject_type ?? 'N/A' }}#{{ $log->subject_id ?? 'N/A' }}</div>
                                </td>
                                <td class="text-body-secondary small">{{ $log->ip_address ?? 'N/A' }}</td>
                                <td class="text-body-secondary small text-nowrap">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary text-nowrap"
                                        data-bs-toggle="modal"
                                        data-bs-target="#auditDetailsModal"
                                        data-audit-details="{{ json_encode($detailPayload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
                                    >
                                        <i class="bi bi-eye me-1"></i> View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-body-secondary py-5">
                                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                    No audit activities match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($auditLogs->hasPages())
            <div class="card-footer bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <span class="small text-body-secondary">
                    Showing {{ $auditLogs->firstItem() }}–{{ $auditLogs->lastItem() }} of {{ number_format($auditLogs->total()) }}
                </span>
                {{ $auditLogs->links() }}
            </div>
        @endif
    </section>

    <div class="modal fade" id="auditDetailsModal" tabindex="-1" aria-labelledby="auditDetailsTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="auditDetailsTitle">Activity details</h2>
                        <p class="small text-body-secondary mb-0" id="auditDetailsDescription"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">Performed by</div><div class="fw-semibold" id="auditDetailsUser"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">Role</div><div class="fw-semibold" id="auditDetailsRole"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">Action</div><div class="fw-semibold" id="auditDetailsAction"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">Employee / User</div><div class="fw-semibold" id="auditDetailsSubjectName"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">Module</div><div class="fw-semibold" id="auditDetailsModule"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">Subject / Record</div><div class="fw-semibold" id="auditDetailsSubject"></div></div>
                        <div class="col-6 col-md-4"><div class="small text-body-secondary">IP address</div><div class="fw-semibold" id="auditDetailsIp"></div></div>
                        <div class="col-12"><div class="small text-body-secondary">Exact date and time</div><div class="fw-semibold" id="auditDetailsDate"></div></div>
                        <div class="col-12"><div class="small text-body-secondary">Browser and device</div><div class="fw-semibold text-break" id="auditDetailsAgent"></div></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <h3 class="h6 fw-bold">Previous values</h3>
                            <dl class="audit-values-list mb-0" id="auditDetailsOld"></dl>
                        </div>
                        <div class="col-12 col-md-6">
                            <h3 class="h6 fw-bold">New values</h3>
                            <dl class="audit-values-list mb-0" id="auditDetailsNew"></dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .audit-action-badge { border: 1px solid transparent; }
        .audit-action-success { color: #176b4d; background: #e6f5ee; border-color: #b7e3cf; }
        .audit-action-primary { color: #245c9e; background: #e8f1fd; border-color: #c4daf8; }
        .audit-action-danger { color: #a3313a; background: #fdeced; border-color: #f5c2c7; }
        .audit-action-purple { color: #6841a5; background: #f1eafd; border-color: #dbcaf6; }
        .audit-action-secondary { color: #526873; background: #edf2f5; border-color: #d7e0e5; }
        .audit-values-list { display: grid; grid-template-columns: minmax(7rem, auto) 1fr; gap: .4rem .75rem; }
        .audit-values-list dt { color: var(--hr-muted); font-weight: 600; overflow-wrap: anywhere; }
        .audit-values-list dd { margin: 0; overflow-wrap: anywhere; }
        .stat-icon.stat-purple { background: #f1eafd; color: #6841a5; }
    </style>

    <script>
        const auditDetailsModal = document.getElementById('auditDetailsModal');

        auditDetailsModal.addEventListener('show.bs.modal', (event) => {
            const details = JSON.parse(event.relatedTarget.dataset.auditDetails);
            const setText = (id, value) => {
                document.getElementById(id).textContent = value || 'N/A';
            };
            const fillValues = (id, values) => {
                const list = document.getElementById(id);
                list.replaceChildren();

                const entries = Object.entries(values || {});
                if (entries.length === 0) {
                    const empty = document.createElement('dd');
                    empty.textContent = 'No recorded changes';
                    list.append(empty);
                    return;
                }

                entries.forEach(([key, value]) => {
                    const term = document.createElement('dt');
                    const description = document.createElement('dd');
                    term.textContent = key.replaceAll('_', ' ');
                    description.textContent = value === null ? '—' : String(value);
                    list.append(term, description);
                });
            };

            setText('auditDetailsTitle', details.description || 'Activity details');
            setText('auditDetailsDescription', details.description);
            setText('auditDetailsUser', details.user);
            setText('auditDetailsRole', details.roles);
            setText('auditDetailsAction', details.action);
            setText('auditDetailsSubjectName', details.subjectName);
            setText('auditDetailsModule', details.module);
            setText('auditDetailsSubject', details.subject);
            setText('auditDetailsIp', details.ip);
            setText('auditDetailsDate', details.created);
            setText('auditDetailsAgent', details.agent);
            fillValues('auditDetailsOld', details.old);
            fillValues('auditDetailsNew', details.new);
        });
    </script>
@endsection
