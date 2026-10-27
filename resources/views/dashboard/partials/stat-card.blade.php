@php
    $label = $card['label'] ?? '';
    $value = $card['value'] ?? 0;
    $icon = $card['icon'] ?? 'bi-graph-up';
    $tone = $card['tone'] ?? 'teal';
    $meta = $card['meta'] ?? null;
@endphp

<div class="dashboard-stat-card h-100 p-3 p-xl-4">
    <div class="d-flex align-items-start justify-content-between gap-3 h-100">
        <div class="d-flex flex-column justify-content-between">
            <div>
                <p class="text-body-secondary small fw-semibold mb-2">{{ $label }}</p>
                <div class="display-6 fw-bold mb-0">{{ number_format((int) $value) }}</div>
            </div>
            @if ($meta)
                <span class="text-body-secondary small mt-3">{{ $meta }}</span>
            @endif
        </div>

        <span class="stat-icon stat-{{ $tone }}">
            <i class="bi {{ $icon }} fs-4"></i>
        </span>
    </div>
</div>
