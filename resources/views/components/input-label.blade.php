@props([
    'label' => null,
    'for' => null,
    'required' => false,
])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'omw-label']) }}>
    {{ $label ?? $slot }}
    @if ($required)
        <span class="text-red-500" aria-hidden="true">*</span>
    @endif
</label>
