<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class StorageSettings extends Settings
{
    public string $filesystem_driver;
    
    public ?string $aws_access_key_id;
    public ?string $aws_secret_access_key;
    public ?string $aws_default_region;
    public ?string $aws_bucket;
    public ?string $aws_url;
    public ?string $aws_endpoint;
    public bool $aws_use_path_style_endpoint;

    public static function group(): string
    {
        return 'storage';
    }

    public static function defaults(): array
    {
        return [
            'filesystem_driver' => 'local',
            'aws_access_key_id' => '',
            'aws_secret_access_key' => '',
            'aws_default_region' => 'us-east-1',
            'aws_bucket' => '',
            'aws_url' => '',
            'aws_endpoint' => '',
            'aws_use_path_style_endpoint' => false,
        ];
    }
}
