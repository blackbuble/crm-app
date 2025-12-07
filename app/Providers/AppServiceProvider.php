<?php

namespace App\Providers;

use App\Models\Quotation;
use App\Models\Customer;
use App\Observers\QuotationObserver;
use App\Observers\CustomerObserver;

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
        //
        Quotation::observe(QuotationObserver::class);
        Customer::observe(CustomerObserver::class);
    }
}
