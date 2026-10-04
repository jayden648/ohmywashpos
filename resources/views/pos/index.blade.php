@php
    $activeCategory = $categories->firstWhere('slug', request('category')) ?? $categories->first();
    $activeCategoryId = $activeCategory?->id;
    $activeServices = $activeCategory?->services ?? collect();
@endphp

<x-app-layout title="Kasir (POS)" subtitle="Pilih layanan, masukkan data sepatu, lalu proses pembayaran.">
    <div x-data="{ category: @js($activeCategory?->slug) }" class="grid gap-4 lg:grid-cols-[1fr_380px] lg:items-start">

        {{-- ================= LEFT: services + shoe details ================= --}}
        <div class="space-y-4">
            <section class="omw-card p-4">
                <h2 class="mb-3 text-base font-bold text-ink">Kategori Layanan</h2>

                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach ($categories as $category)
                        <button type="button"
                                @click="category = @js($category->slug)"
                                class="omw-btn !min-h-[38px] !px-3 !py-1.5 text-xs
                                       category === @js($category->slug)
                                           ? 'border-brand bg-brand text-ink'
                                           : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50'">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($categories as $category)
                        <div x-show="category === @js($category->slug)" x-cloak class="contents">
                            @foreach ($category->services as $service)
                                <form method="POST" action="{{ route('pos.cart') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="service_id" value="{{ $service->id }}">

                                    <button type="submit" class="omw-service-card">
                                        <span class="font-bold text-ink">{{ $service->name }}</span>
                                        <span class="mt-1 text-xs text-gray-500">
                                            {{ $service->formatted_price }} · {{ $service->duration_label }}
                                        </span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @empty
                        <p class="omw-col-span-full py-6 text-center text-sm text-gray-500">
                            Belum ada kategori layanan.
                        </p>
                    @endforelse
                </div>
            </section>

            @include('pos.partials.shoe-details')
        </div>

        {{-- ================= RIGHT: cart & checkout ================= --}}
        @include('pos.partials.cart')
    </div>
</x-app-layout>