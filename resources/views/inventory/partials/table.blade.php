{{-- Stock levels, editable inline. --}}
<div class="omw-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="omw-th">Barang</th>
                    <th class="omw-th text-center">Jumlah</th>
                    <th class="omw-th text-center">Minimum</th>
                    <th class="omw-th text-center">Status</th>
                    <th class="omw-th"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr class="transition hover:bg-brand-soft/40 {{ $item->is_active ? '' : 'opacity-50' }}">
                        <td class="omw-td font-medium text-ink">{{ $item->name }}</td>

                        <td class="omw-td text-center">
                            <form method="POST" action="{{ route('inventory.update', $item) }}"
                                  class="flex items-center justify-center gap-1">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="name" value="{{ $item->name }}">
                                <input type="hidden" name="unit" value="{{ $item->unit }}">
                                <input type="hidden" name="minimum_stock" value="{{ $item->minimum_stock }}">

                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="0"
                                       aria-label="Jumlah {{ $item->name }}"
                                       class="w-20 rounded-lg border-gray-300 px-2 py-1 text-center text-sm
                                              focus:border-brand focus:ring-brand">

                                <button type="submit" class="rounded-lg border border-gray-300 p-1.5 hover:bg-gray-50"
                                        title="Simpan jumlah {{ $item->name }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </button>
                            </form>
                        </td>

                        <td class="omw-td text-center">{{ $item->minimum_stock }} {{ $item->unit }}</td>

                        <td class="omw-td text-center">
                            <span class="omw-badge {{ $item->isLowStock() ? 'bg-amber-100 text-amber-800 ring-amber-200' : 'bg-emerald-100 text-emerald-800 ring-emerald-200' }}">
                                {{ $item->isLowStock() ? 'Menipis' : 'Aman' }}
                            </span>
                        </td>

                        <td class="omw-td text-right">
                            <form method="POST" action="{{ route('inventory.destroy', $item) }}"
                                  onsubmit="return confirm('Nonaktifkan barang {{ $item->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                    Nonaktifkan
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">
                            Belum ada barang inventori.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $items->links() }}</div>
    @endif
</div>