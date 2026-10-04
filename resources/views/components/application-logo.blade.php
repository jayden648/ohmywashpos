{{-- OhMyWash brand mark. Swap this component for an <img> of the official
     logo asset as soon as one is available. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand text-ink">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"
             stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true">
            <path d="M12 2.5c1.6 3.4 5.5 4.4 5.5 9a5.5 5.5 0 1 1-11 0c0-2.2 1.3-3.6 2.6-4.8.2 1.6.9 2.4 1.8 2.6-.3-2.6.2-4.9 1.1-6.8Z" />
        </svg>
    </span>

    <span class="leading-none">
        <span class="block text-lg font-extrabold tracking-tight {{ ($dark ?? false) ? 'text-white' : 'text-ink' }}">
            OH<span class="{{ ($dark ?? false) ? 'text-brand' : 'text-brand-dark' }}">MY</span>WASH
        </span>

        @if (($withTagline ?? false))
            <span class="mt-1 block text-[10px] font-medium uppercase tracking-widest {{ ($dark ?? false) ? 'text-gray-400' : 'text-gray-500' }}">
                Shoe Laundry
            </span>
        @endif
    </span>
</span>