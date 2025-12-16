<?php

namespace Tests\Feature;

use App\Settings\StorageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_settings_can_be_saved_and_retrieved()
    {
        // Simulate Settings Migration
        $this->artisan('migrate');

        $settings = resolve(StorageSettings::class);
        
        $settings->filesystem_driver = 's3';
        $settings->aws_bucket = 'my-test-bucket';
        $settings->save();
        
        $this->assertDatabaseHas('settings', [
            'group' => 'storage',
            'name' => 'filesystem_driver',
        ]);
        
        $freshSettings = resolve(StorageSettings::class);
        $this->assertEquals('s3', $freshSettings->filesystem_driver);
        $this->assertEquals('my-test-bucket', $freshSettings->aws_bucket);
    }
}
