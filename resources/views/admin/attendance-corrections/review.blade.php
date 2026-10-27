@extends('layouts.app')

@section('title', 'Review Attendance Correction')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Review Correction</h1>
            <p class="text-body-secondary mb-0">Review and decide on this attendance correction request.</p>
        </div>

        <a href="{{ route('admin.attendance-corrections.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <h5 class="mb-3">Correction Details</h5>
                    <div class="mb-2">
                        <label class="form-label">Employee</label>
                        <p class="fw-semibold mb-0">{{ $correction->employee->user?->name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Attendance Date</label>
                        <p class="mb-0">{{ $correction->attendance_date?->format('M d, Y') }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Requested Check-in</label>
                        <p class="mb-0">{{ $correction->requested_check_in_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Requested Check-out</label>
                        <p class="mb-0">{{ $correction->requested_check_out_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Requested Status</label>
                        <p class="mb-0">{{ ucfirst($correction->requested_status ?? '—') }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Reason</label>
                        <p class="mb-0">{{ $correction->reason }}</p>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <h5 class="mb-3">Decision</h5>

                    @if (session('success'))
                        <div class="alert alert-success" role="status">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            Please review the highlighted fields and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.attendance-corrections.update', $correction) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label">Decision <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reviewer_comments" class="form-label">Comments</label>
                            <textarea name="reviewer_comments" id="reviewer_comments" rows="4" class="form-control @error('reviewer_comments') is-invalid @enderror">{{ old('reviewer_comments') }}</textarea>
                            @error('reviewer_comments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.attendance-corrections.index') }}" class="btn btn-light border">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Decision</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection