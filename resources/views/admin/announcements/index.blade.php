@extends('layouts.app')

@section('title', 'Company Announcements')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Company Announcements</h1>
            <p class="text-body-secondary mb-0">Manage company-wide announcements.</p>
        </div>

        @if (auth()->user()?->hasPermission('announcement.create'))
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Announcement
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
                            <th scope="col">Title</th>
                            <th scope="col">Content</th>
                            <th scope="col">Status</th>
                            <th scope="col">Publish At</th>
                            <th scope="col">Expires At</th>
                            <th scope="col">Created By</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $announcement->title }}</td>
                                <td class="text-body-secondary small">{{ Str::limit($announcement->content, 100) }}</td>
                                <td>
                                    <span class="badge bg-{{ $announcement->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $announcement->status === 'active' ? 'success' : 'secondary' }} border border-{{ $announcement->status === 'active' ? 'success' : 'secondary' }}-subtle">
                                        {{ ucfirst($announcement->status) }}
                                    </span>
                                </td>
                                <td class="text-body-secondary small">{{ $announcement->publish_at?->format('M d, Y H:i') ?? 'Immediate' }}</td>
                                <td class="text-body-secondary small">{{ $announcement->expires_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                                <td class="text-body-secondary small">{{ $announcement->creator?->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if (auth()->user()?->hasPermission('announcement.update'))
                                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @endif

                                        @if (auth()->user()?->hasPermission('announcement.delete'))
                                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this announcement?')">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-body-secondary py-5">No announcements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($announcements->hasPages())
            <div class="card-footer bg-white">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
@endsection