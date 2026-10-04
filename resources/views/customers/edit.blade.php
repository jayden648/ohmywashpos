<x-app-layout title="Edit Pelanggan" :subtitle="$customer->name">
    <x-slot:actions>
        <a href="{{ route('customers.show', $customer) }}" class="omw-btn-secondary">Kembali</a>
    </x-slot:actions>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="omw-card p-5">
            @csrf
            @method('PUT')
            @include('customers._form')
        </form>
    </div>
</x-app-layout>