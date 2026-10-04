<x-app-layout>
    <x-slot name="title">{{ __('Edit account') }}</x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl px-6">
            <h1 class="text-2xl font-semibold text-gray-800">{{ __('Edit account') }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $user->name }} &mdash; {{ $user->email }}
            </p>

            <div class="mt-8 bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('admin.users._form', ['roles' => $roles, 'user' => $user])

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                        <a href="{{ route('admin.users.index') }}"
                           class="text-sm text-gray-600 underline hover:text-gray-900">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>