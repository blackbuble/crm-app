<?php

namespace App\Providers;

use App\Settings\StorageSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class StorageConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run if database is connected and settings table potentially exists
        // We use a try-catch to avoid breaking artisan commands during migration/setup
        try {
            // Check if settings class exists and can be loaded
            if (class_exists(StorageSettings::class) && Schema::hasTable('settings')) {
                $settings = app(StorageSettings::class);

                // Override Default Disk
                if ($settings->filesystem_driver) {
                    Config::set('filesystems.default', $settings->filesystem_driver);
                }

                // Override S3 Config if driver is S3
                if ($settings->filesystem_driver === 's3') {
                    Config::set('filesystems.disks.s3.key', $settings->aws_access_key_id);
                    Config::set('filesystems.disks.s3.secret', $settings->aws_secret_access_key);
                    Config::set('filesystems.disks.s3.region', $settings->aws_default_region);
                    Config::set('filesystems.disks.s3.bucket', $settings->aws_bucket);
                    
                    if ($settings->aws_endpoint) {
                         Config::set('filesystems.disks.s3.endpoint', $settings->aws_endpoint);
                    }
                    
                    if ($settings->aws_url) {
                         Config::set('filesystems.disks.s3.url', $settings->aws_url);
                    }
                    
                    Config::set('filesystems.disks.s3.use_path_style_endpoint', $settings->aws_use_path_style_endpoint);
                }
            }
        } catch (\Throwable $e) {
            // Fail silently if DB not ready or settings not migrated yet
            // This ensures 'php artisan migrate' or 'composer install' doesn't crash
        }
    }
}
