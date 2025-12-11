<?php
// database/seeders/SettingsSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => config('app.name', 'Your Company'),
            'company_email' => 'hello@example.com',
            'company_phone' => '+1234567890',
            'company_address' => '',
            'tax_id' => '',
            'company_logo' => null,
            'bank_accounts' => [],
            'quotation_terms' => '',
            'quotation_footer' => '',
        ];

        foreach ($settings as $name => $value) {
            // Check if setting already exists
            $exists = DB::table('settings')
                ->where('group', 'general')
                ->where('name', $name)
                ->exists();

            if (!$exists) {
                DB::table('settings')->insert([
                    'group' => 'general',
                    'name' => $name,
                    'payload' => json_encode(['value' => $value]),
                    'locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Settings seeded successfully!');
    }
}