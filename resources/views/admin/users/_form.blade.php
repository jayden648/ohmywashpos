@props(['roles', 'user' => null])

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $user?->name)" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                      :value="old('email', $user?->email)" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" :value="__('Role')" />
        <select id="role" name="role"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach ($roles as $role)
                <option value="{{ $role->value }}"
                        @selected(old('role', $user?->role?->value) === $role->value)>
                    {{ $role->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$user ? __('New password (leave blank to keep current)') : __('Password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                      :required="! $user" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                      class="mt-1 block w-full" :required="! $user" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>
</div>