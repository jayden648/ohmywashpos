<button {{ $attributes->merge(['type' => 'submit', 'class' => 'omw-btn-primary']) }}>
    {{ $slot }}
</button>
