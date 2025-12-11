<?php
// app/Settings/GeneralSettings.php
namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $company_name;
    public string $company_email;
    public string $company_phone;
    public ?string $company_address;
    public ?string $tax_id;
    public ?string $company_logo;
    public array $bank_accounts;
    public ?string $quotation_terms;
    public ?string $quotation_footer;

    public static function group(): string
    {
        return 'general';
    }
    
    public static function defaults(): array
    {
        return [
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
    }
}