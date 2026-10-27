@extends('layouts.app')

@section('title', 'Company Announcements')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Company Announcements</h1>
            <p class="text-body-secondary mb-0">Read the latest company updates.</p>
        </div>

        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">Back to dashboard</a>
    </div>

    @if ($announcements->isNotEmpty())
        <div class="row g-3">
            @foreach ($announcements as $announcement)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-{{ $announcement->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $announcement->status === 'active' ? 'success' : 'secondary' }} border border-{{ $announcement->status === 'active' ? 'success' : 'secondary' }}-subtle">
                                    {{ ucfirst($announcement->status) }}
                                </span>
                                <span class="text-body-secondary small">{{ $announcement->publish_at?->format('M d, Y') ?? 'Immediate' }}</span>
                            </div>
                            <h5 class="card-title">{{ $announcement->title }}</h5>
                            <p class="card-text text-body-secondary">{{ $announcement->content }}</p>
                            @if ($announcement->expires_at)
                                <div class="mt-2">
                                    <small class="text-body-secondary">Expires: {{ $announcement->expires_at->format('M d, Y') }}</small>
                                </div>
                            @endif
                            <div class="mt-3">
                                <small class="text-body-secondary">By: {{ $announcement->creator?->name ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-body-secondary py-5">
            <i class="bi bi-bell-slash fs-1 mb-3 opacity-25"></i>
            <p>No announcements available at this time.</p>
        </div>
    @endif
@endsection