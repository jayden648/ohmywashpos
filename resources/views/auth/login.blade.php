<x-guest-layout title="Masuk — OhMyWash POS">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-extrabold tracking-tight text-ink">Masuk ke OhMyWash</h1>
        <p class="mt-1 text-sm text-gray-500">
            Sistem kasir & laundry sepatu profesional.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" label="Email" :required="true" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autofocus autocomplete="username" placeholder="nama@ohmywash.test" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" label="Password" :required="true" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-gray-300 text-brand focus:ring-brand">
            <span class="text-sm text-gray-600">Ingat saya</span>
        </label>

        <button type="submit" class="omw-btn-primary w-full">Masuk</button>

        <div class="flex items-center justify-between pt-1 text-sm">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-gray-500 hover:text-ink">
                    Lupa password?
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>