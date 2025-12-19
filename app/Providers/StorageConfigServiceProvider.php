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
        try {
            if (class_exists(StorageSettings::class) && Schema::hasTable('settings')) {
                $settings = app(StorageSettings::class);

                if ($settings->filesystem_driver === 's3') {
                    \Illuminate\Support\Facades\Log::info('Applying S3/R2 Storage Config', ['bucket' => $settings->aws_bucket]);
                    
                    $s3Config = [
                        'driver' => 's3',
                        'key' => $settings->aws_access_key_id,
                        'secret' => $settings->aws_secret_access_key,
                        'region' => $settings->aws_default_region,
                        'bucket' => $settings->aws_bucket,
                        'url' => $settings->aws_url,
                        'endpoint' => $settings->aws_endpoint,
                        'use_path_style_endpoint' => $settings->aws_use_path_style_endpoint,
                        'throw' => false,
                        'visibility' => 'public',
                    ];

                    // Core Filesystem
                    Config::set('filesystems.default', 's3');
                    Config::set('filesystems.disks.s3', $s3Config);
                    Config::set('filesystems.disks.public', $s3Config); // Critical: Overwrite local public disk with S3
                    
                    // Filament & Plugins
                    Config::set('filament.default_filesystem_disk', 's3');
                    Config::set('media-library.disk_name', 's3');
                    Config::set('livewire.temporary_file_upload.disk', 's3');
                    
                    // Force ENV variables (sometimes needed for certain helpers)
                    putenv("FILESYSTEM_DISK=s3");
                    $_ENV['FILESYSTEM_DISK'] = 's3';

                    // Clear resolved instances to force re-reading config
                    if ($this->app->resolved('storage')) {
                        \Illuminate\Support\Facades\Storage::forgetDisk('s3');
                        \Illuminate\Support\Facades\Storage::forgetDisk('public');
                        \Illuminate\Support\Facades\Storage::forgetDisk(null);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::info('Applying Local Storage Config');
                    Config::set('filesystems.default', $settings->filesystem_driver ?? 'local');
                    putenv("FILESYSTEM_DISK=" . ($settings->filesystem_driver ?? 'local'));
                }
            }
        } catch (\Throwable $e) {
            // Fail silently during boot
        }
    }
}
