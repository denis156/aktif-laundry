<?php

namespace App\Providers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiPromo;
use App\Models\User;
use App\Observers\ChatMessageObserver;
use App\Observers\ConversationObserver;
use App\Observers\KurirObserver;
use App\Observers\PelangganObserver;
use App\Observers\TransaksiObserver;
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
        Transaksi::observe(TransaksiObserver::class);
        TransaksiPromo::observe(TransaksiPromoObserver::class);
        Conversation::observe(ConversationObserver::class);
        ChatMessage::observe(ChatMessageObserver::class);
    }
}
