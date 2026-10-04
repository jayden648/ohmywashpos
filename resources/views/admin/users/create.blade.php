<x-app-layout>
    <x-slot name="title">{{ __('Create account') }}</x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl px-6">
            <h1 class="text-2xl font-semibold text-gray-800">{{ __('Create account') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Add a team member and choose the role they will use.') }}</p>

            <div class="mt-8 bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    @include('admin.users._form', ['roles' => $roles, 'user' => null])

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create account') }}</x-primary-button>
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