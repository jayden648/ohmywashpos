@props(['value' => null])

<input
    @if ($attributes->has('type') && $attributes->get('type') === 'checkbox') @else value="{{ $value }}" @endif
    {{ $attributes->merge(['class' => 'omw-input']) }}
/>
