<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Product;
use App\Observers\UserObserver;
use App\Observers\InvoiceObserver;
use App\Observers\ProductObserver;


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
        User::observe(UserObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Product::observe(ProductObserver::class);
    }
}
