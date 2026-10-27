@extends('layouts.app')

@section('title', 'Attendance Corrections')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Corrections</h1>
            <p class="text-body-secondary mb-0">Review and manage attendance correction requests.</p>
        </div>

        @if (auth()->user()?->hasPermission('attendance.correction'))
            <a href="{{ route('admin.attendance-corrections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Request Correction
            </a>
        @endif
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
                            <th scope="col">Employee</th>
                            <th scope="col">Date</th>
                            <th scope="col">Requested Check-in</th>
                            <th scope="col">Requested Check-out</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($corrections as $correction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $correction->employee->user?->name ?? 'N/A' }}</td>
                                <td>{{ $correction->attendance_date?->format('M d, Y') }}</td>
                                <td class="text-body-secondary small">{{ $correction->requested_check_in_at?->format('h:i A') ?? '—' }}</td>
                                <td class="text-body-secondary small">{{ $correction->requested_check_out_at?->format('h:i A') ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $correction->status === 'approved' ? 'success' : ($correction->status === 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $correction->status === 'approved' ? 'success' : ($correction->status === 'rejected' ? 'danger' : 'warning') }} border border-{{ $correction->status === 'approved' ? 'success' : ($correction->status === 'rejected' ? 'danger' : 'warning') }}-subtle">
                                        {{ ucfirst($correction->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($correction->status === 'pending' && auth()->user()?->hasPermission('attendance.view'))
                                        <a href="{{ route('admin.attendance-corrections.review', $correction) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-body-secondary py-5">No attendance corrections found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($corrections->hasPages())
            <div class="card-footer bg-white">
                {{ $corrections->links() }}
            </div>
        @endif
    </div>
@endsection