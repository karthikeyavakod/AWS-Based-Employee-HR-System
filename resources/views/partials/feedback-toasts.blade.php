@php
    $feedbackMessages = collect([
        ['type' => 'success', 'message' => session('success'), 'icon' => 'check-circle-fill'],
        ['type' => 'danger', 'message' => session('error'), 'icon' => 'exclamation-triangle-fill'],
        ['type' => 'info', 'message' => session('status'), 'icon' => 'info-circle-fill'],
        ['type' => 'warning', 'message' => session('warning'), 'icon' => 'exclamation-circle-fill'],
    ])->filter(fn ($feedback) => filled($feedback['message']));

    if ($errors->any()) {
        $feedbackMessages->push([
            'type' => 'danger',
            'message' => 'Please review the highlighted fields and try again.',
            'icon' => 'exclamation-triangle-fill',
        ]);
    }
@endphp

@if ($feedbackMessages->isNotEmpty())
    <div class="feedback-toast-container" aria-live="polite" aria-atomic="false">
        @foreach ($feedbackMessages as $feedback)
            <div
                class="toast feedback-toast text-bg-{{ $feedback['type'] }} border-0 shadow"
                role="{{ $feedback['type'] === 'danger' ? 'alert' : 'status' }}"
                aria-live="{{ $feedback['type'] === 'danger' ? 'assertive' : 'polite' }}"
                aria-atomic="true"
                data-feedback-toast
                data-bs-delay="6000"
            >
                <div class="d-flex align-items-center">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-{{ $feedback['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $feedback['message'] }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .feedback-toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1090;
            display: grid;
            gap: .75rem;
            width: min(26rem, calc(100vw - 2rem));
        }

        .feedback-toast {
            width: 100%;
            border-radius: .75rem;
        }
    </style>

    <script>
        document.querySelectorAll('[data-feedback-toast]').forEach((toastElement) => {
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        });

        const feedbackText = [...document.querySelectorAll('[data-feedback-toast] .toast-body span')]
            .map((message) => message.textContent.trim());

        document.querySelectorAll('.alert').forEach((alert) => {
            if (feedbackText.includes(alert.textContent.trim())) {
                alert.remove();
            }
        });
    </script>
@endif
