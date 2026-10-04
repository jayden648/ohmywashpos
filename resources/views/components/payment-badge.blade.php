@props([
    'status' => null,
    'label' => null,
])

<span {{ $attributes->merge(['class' => 'omw-badge '.($status?->badgeClasses() ?? 'bg-gray-100 text-gray-700 ring-gray-200')]) }}>
    {{ $label ?? $status?->label() ?? '—' }}
</span>