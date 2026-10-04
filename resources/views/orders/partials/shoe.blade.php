{{-- The shoe record captured at the till when the order was created. --}}
<section class="omw-card p-4">
    <h2 class="mb-3 text-base font-bold text-ink">Data Sepatu</h2>

    @forelse ($order->shoes as $shoe)
        <dl class="grid gap-2 text-sm sm:grid-cols-2">
            @foreach ([
                'Brand' => $shoe->brand,
                'Model' => $shoe->model,
                'Warna' => $shoe->color,
                'Ukuran' => $shoe->size,
                'Material' => $shoe->material,
                'Kondisi sebelum dicuci' => $shoe->condition_before,
            ] as $label => $value)
                <div>
                    <dt class="omw-label">{{ $label }}</dt>
                    <dd class="text-ink">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach

            @foreach (['damage_notes' => 'Catatan Kerusakan', 'customer_notes' => 'Catatan Pelanggan'] as $field => $label)
                @if ($shoe->{$field})
                    <div class="sm:col-span-2">
                        <dt class="omw-label">{{ $label }}</dt>
                        <dd class="text-ink">{{ $shoe->{$field} }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    @empty
        <p class="text-sm text-gray-500">Tidak ada data sepatu tercatat.</p>
    @endforelse
</section>