@props([
    'status' => null,
    'label' => null,
])

@php
    $classes = $status?->badgeClasses() ?? 'bg-gray-100 text-gray-700 ring-gray-200';
@endphp

<span {{ $attributes->merge(['class' => 'omw-badge '.$classes]) }}>
    {{ $label ?? $status?->label() ?? '—' }}
</span>