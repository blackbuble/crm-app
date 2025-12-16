<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('storage.filesystem_driver', 'local');
        $this->migrator->add('storage.aws_access_key_id', '');
        $this->migrator->add('storage.aws_secret_access_key', '');
        $this->migrator->add('storage.aws_default_region', 'us-east-1');
        $this->migrator->add('storage.aws_bucket', '');
        $this->migrator->add('storage.aws_url', '');
        $this->migrator->add('storage.aws_endpoint', '');
        $this->migrator->add('storage.aws_use_path_style_endpoint', false);
    }
};
