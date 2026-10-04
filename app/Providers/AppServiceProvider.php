<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @role('admin', 'cashier') ... @else ... @endrole
        Blade::if('role', function (string ...$roles): bool {
            $user = auth()->user();

            return $user !== null && $user->hasAnyRole(...$roles);
        });

        // @unlessrole('admin') ... @endunlessrole
        Blade::if('unlessrole', function (string ...$roles): bool {
            $user = auth()->user();

            return $user === null || ! $user->hasAnyRole(...$roles);
        });
    }
}
