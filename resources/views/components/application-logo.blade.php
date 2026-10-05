{{--
    The official OhMyWash logo.

    One single source of truth: public/images/ohmywash-logo.png, served as a
    static asset so it never depends on a Vite-generated filename.

    $dark places the logo on a light tile. The artwork contains a black shoe
    glyph, so it would otherwise disappear against the black sidebar and the
    dark hero pages. The PNG itself is never recoloured, filtered or overlaid.
--}}
@props([
    'dark' => false,
    'withTagline' => false,
    'size' => 'w-36',
])

<span class="inline-flex flex-col items-center">
    <span class="{{ $dark ? 'rounded-xl bg-white px-3 py-2' : '' }}">
        <img
            {{ $attributes->merge(['class' => 'h-auto object-contain '.$size]) }}
            src="{{ asset('images/ohmywash-logo.png') }}"
            alt="OhMyWash"
            width="1999"
            height="787"
        >
    </span>

    @if ($withTagline)
        <span class="mt-2 text-[10px] font-medium uppercase tracking-widest {{ $dark ? 'text-gray-400' : 'text-gray-500' }}">
            Shoe Laundry
        </span>
    @endif
</span>