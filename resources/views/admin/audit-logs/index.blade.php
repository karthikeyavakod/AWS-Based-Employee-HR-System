@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Audit Logs</h1>
            <p class="text-body-secondary mb-0">Review system-wide administrative actions.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">User</th>
                            <th scope="col">Action</th>
                            <th scope="col">Module</th>
                            <th scope="col">Subject</th>
                            <th scope="col">IP Address</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $log)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                    <div class="small text-body-secondary">{{ $log->user?->email ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td class="text-body-secondary small">{{ ucfirst(str_replace('_', ' ', $log->module)) }}</td>
                                <td class="text-body-secondary small">
                                    {{ $log->subject_type ?? 'N/A' }}#{{ $log->subject_id ?? 'N/A' }}
                                </td>
                                <td class="text-body-secondary small">{{ $log->ip_address ?? 'N/A' }}</td>
                                <td class="text-body-secondary small">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-body-secondary py-5">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($auditLogs->hasPages())
            <div class="card-footer bg-white">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
@endsection