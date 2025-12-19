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
        if (config('app.env') === 'production' || config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Apply dynamic storage settings
        try {
            if (class_exists(\App\Settings\StorageSettings::class)) {
                $storageSettings = app(\App\Settings\StorageSettings::class);
                
                config([
                    'filesystems.default' => $storageSettings->filesystem_driver,
                    'filesystems.disks.s3.key' => $storageSettings->aws_access_key_id,
                    'filesystems.disks.s3.secret' => $storageSettings->aws_secret_access_key,
                    'filesystems.disks.s3.region' => $storageSettings->aws_default_region,
                    'filesystems.disks.s3.bucket' => $storageSettings->aws_bucket,
                    'filesystems.disks.s3.url' => $storageSettings->aws_url,
                    'filesystems.disks.s3.endpoint' => $storageSettings->aws_endpoint,
                    'filesystems.disks.s3.use_path_style_endpoint' => $storageSettings->aws_use_path_style_endpoint,
                ]);
            }
        } catch (\Exception $e) {
            // Settings table might not exist during migration
        }

        FollowUp::observe(FollowUpObserver::class);
        Quotation::observe(QuotationObserver::class);
        Customer::observe(CustomerObserver::class);
        KpiTarget::observe(KpiTargetObserver::class);
    }
}
