<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Policies\CustomerPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Explicit policy bindings, so authorisation never falls back to guessing.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        User::class => UserPolicy::class,
        Customer::class => CustomerPolicy::class,
        Service::class => ServicePolicy::class,
        ServiceCategory::class => ServiceCategoryPolicy::class,
        Order::class => OrderPolicy::class,
        Payment::class => PaymentPolicy::class,
        InventoryItem::class => InventoryItemPolicy::class,
    ];

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
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

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

        // Format money as Indonesian Rupiah wherever it is echoed.
        Blade::directive('rupiah', function (string $amount): string {
            return "<?php echo 'Rp'.number_format((float) ({$amount}), 0, ',', '.'); ?>";
        });
    }
}
