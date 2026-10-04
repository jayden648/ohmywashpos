<x-app-layout title="Pelanggan" subtitle="Kelola basis pelanggan OhMyWash.">
    <x-slot:actions>
        <a href="{{ route('customers.create') }}" class="omw-btn-primary">+ Pelanggan Baru</a>
    </x-slot:actions>

    {{-- Search runs on the server. --}}
    <form method="GET" class="omw-card mb-4 flex gap-2 p-4">
        <label for="search" class="sr-only">Cari pelanggan</label>
        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="omw-input flex-1"
               placeholder="Cari nama, WhatsApp, atau kode pelanggan…">
        <button type="submit" class="omw-btn-dark">Cari</button>
    </form>

    <div class="omw-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="omw-th">ID</th>
                        <th class="omw-th">Nama</th>
                        <th class="omw-th">WhatsApp</th>
                        <th class="omw-th text-center">Pesanan</th>
                        <th class="omw-th text-right">Total Belanja</th>
                        <th class="omw-th"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        <tr class="transition hover:bg-brand-soft/40 {{ $customer->is_active ? '' : 'opacity-50' }}">
                            <td class="omw-td font-mono text-xs">{{ $customer->customer_code }}</td>
                            <td class="omw-td font-medium text-ink">{{ $customer->name }}</td>
                            <td class="omw-td">{{ $customer->phone }}</td>
                            <td class="omw-td text-center">{{ $customer->paid_orders_count }}</td>
                            <td class="omw-td text-right font-semibold">@rupiah($customer->total_spending ?? 0)</td>
                            <td class="omw-td text-right">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="omw-btn-secondary !min-h-[34px] !px-3">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                Pelanggan tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $customers->links() }}</div>
        @endif
    </div>
</x-app-layout>