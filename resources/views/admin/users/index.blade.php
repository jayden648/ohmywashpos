<x-app-layout>
    <x-slot name="title">{{ __('Users') }}</x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">{{ __('Users') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Manage who can sign in and what they can do.') }}</p>
                </div>
                <x-primary-button tag="a" href="{{ route('admin.users.create') }}">
                    {{ __('New account') }}
                </x-primary-button>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mt-8 bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-56">
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="search" class="mt-1 block w-full"
                                      :value="$filters['search'] ?? null" placeholder="Name or email" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role"
                                class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All roles') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}"
                                        @selected(($filters['role'] ?? null) === $role->value)>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-secondary-button>{{ __('Filter') }}</x-secondary-button>

                    @if (($filters['search'] ?? null) || ($filters['role'] ?? null))
                        <a href="{{ route('admin.users.index') }}"
                           class="text-sm text-gray-600 underline hover:text-gray-900">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </form>
            </div>
            <div class="mt-6 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Email') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Role') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if ($user->is(auth()->user()))
                                        <span class="ms-1 text-xs text-gray-500">({{ __('you') }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $user->role->label() }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    @can('update', $user)
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="text-indigo-600 underline hover:text-indigo-800">
                                            {{ __('Edit') }}
                                        </a>
                                    @endcan

                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              class="inline"
                                              onsubmit="return confirm('{{ __('Remove this account?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ms-3 text-red-600 underline hover:text-red-800">
                                                {{ __('Remove') }}
                                            </button>
                                        </form>
                                    @else
                                        @php($reason = Gate::getPolicyFor(auth()->user(), \App\Models\User::class)?->deleteReason(auth()->user(), $user))
                                        @if ($reason && ! $reason->allowed())
                                            <span class="ms-3 text-xs text-gray-400">{{ $reason->message() }}</span>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    {{ __('No users found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>