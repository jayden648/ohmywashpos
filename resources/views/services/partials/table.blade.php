{{-- The catalogue. Prices are edited inline and saved straight to MySQL. --}}
<div class="omw-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="omw-th">Layanan</th>
                    <th class="omw-th">Kategori</th>
                    <th class="omw-th text-right">Harga</th>
                    <th class="omw-th text-center">Estimasi</th>
                    <th class="omw-th text-center">Status</th>
                    @if ($canManage)
                        <th class="omw-th"><span class="sr-only">Aksi</span></th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($services as $service)
                    <tr class="transition hover:bg-brand-soft/40 {{ $service->is_active ? '' : 'opacity-50' }}">
                        <td class="omw-td font-medium text-ink">{{ $service->name }}</td>

                        <td class="omw-td">
                            <span class="omw-badge bg-gray-100 text-gray-700 ring-gray-200">
                                {{ $service->serviceCategory->name }}
                            </span>
                        </td>

                        @if ($canManage)
                            <td class="omw-td text-right">
                                <form method="POST" action="{{ route('services.update', $service) }}"
                                      class="flex items-center justify-end gap-1">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="service_category_id" value="{{ $service->service_category_id }}">
                                    <input type="hidden" name="name" value="{{ $service->name }}">
                                    <input type="hidden" name="estimated_duration" value="{{ $service->estimated_duration }}">
                                    <input type="hidden" name="is_active" value="1">

                                    <input type="number" name="price" value="{{ (int) $service->price }}" min="0" step="500"
                                           aria-label="Harga {{ $service->name }}"
                                           class="w-28 rounded-lg border-gray-300 px-2 py-1 text-right text-sm
                                                  focus:border-brand focus:ring-brand">

                                    <button type="submit" class="rounded-lg border border-gray-300 p-1.5 hover:bg-gray-50"
                                            title="Simpan harga {{ $service->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        @else
                            <td class="omw-td text-right font-semibold">{{ $service->formatted_price }}</td>
                        @endif

                        <td class="omw-td text-center">{{ $service->duration_label }}</td>

                        <td class="omw-td text-center">
                            <span class="omw-badge {{ $service->is_active ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        @if ($canManage)
                            <td class="omw-td text-right">
                                <form method="POST" action="{{ route('services.destroy', $service) }}"
                                      onsubmit="return confirm('{{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }} layanan {{ $service->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                        {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                            Belum ada layanan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($services->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $services->links() }}</div>
    @endif
</div>