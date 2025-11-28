<?php

namespace App\Providers;

use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\TransaksiPromo;
use App\Models\User;
use App\Observers\KurirObserver;
use App\Observers\PelangganObserver;
use App\Observers\TransaksiPromoObserver;
use App\Observers\UserObserver;
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
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        // Register Observers
        User::observe(UserObserver::class);
        Pelanggan::observe(PelangganObserver::class);
        Kurir::observe(KurirObserver::class);
        TransaksiPromo::observe(TransaksiPromoObserver::class);
    }
}
