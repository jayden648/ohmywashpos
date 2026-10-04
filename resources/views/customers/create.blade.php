<x-app-layout title="Pelanggan Baru" subtitle="Tambahkan pelanggan ke basis data OhMyWash.">
    <x-slot:actions>
        <a href="{{ route('customers.index') }}" class="omw-btn-secondary">Kembali</a>
    </x-slot:actions>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('customers.store') }}" class="omw-card p-5">
            @csrf
            @include('customers._form')
        </form>
    </div>
</x-app-layout>