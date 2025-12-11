<?php

namespace App\Providers;

use App\Models\FollowUp;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\KpiTarget;
use App\Observers\FollowUpObserver;
use App\Observers\QuotationObserver;
use App\Observers\CustomerObserver;
use App\Observers\KpiTargetObserver;

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
        FollowUp::observe(FollowUpObserver::class);
        Quotation::observe(QuotationObserver::class);
        Customer::observe(CustomerObserver::class);
        KpiTarget::observe(KpiTargetObserver::class);
    }
}
