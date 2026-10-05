@php
    // Auth::user() keeps the type resolvable for static analysers; the
    // auth()->user() helper returns an AuthManager that only proxies
    // `user` through __call.
    $user = Illuminate\Support\Facades\Auth::user();

    // Presentation only: routes are also protected by middleware/policies.
    $visibleItems = collect(App\Support\Navigation::items())
        ->filter(fn (array $item): bool => $user !== null && $user->hasAnyRole(...$item['roles']));
@endphp

<nav aria-label="Navigasi utama" class="flex h-full flex-col bg-ink text-white">
    <div class="flex items-center px-4 py-5">
            {{-- Sidebar: compact, on a light tile against the black background. --}}
            <x-application-logo :dark="true" size="w-36" />
    </div>

    <p class="px-4 pb-5 text-[11px] leading-relaxed text-gray-400">
        Bukan hanya bersih, tapi terlahir kembali dengan elegansi.
    </p>

    <ul class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
        @foreach ($visibleItems as $item)
            @php $isActive = request()->routeIs($item['route']); @endphp

            <li>
                <a href="{{ route($item['route']) }}"
                   @if ($isActive) aria-current="page" @endif
                   class="omw-sidebar-link {{ $isActive ? 'omw-sidebar-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="{{ App\Support\Navigation::icon($item['icon']) }}" />
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    @if ($user)
        <div class="border-t border-white/10 px-4 py-4">
            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
            <p class="mt-0.5 text-xs text-gray-400">{{ $user->role->label() }}</p>

            {{-- Signing out is a POST so the session is invalidated server-side. --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="omw-sidebar-link w-full">Keluar</button>
            </form>
        </div>
    @endif
</nav>