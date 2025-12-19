<?php

if (!function_exists('get_setting')) {
    /**
     * Get a setting value from database
     * 
     * @param string $name Setting name
     * @param mixed $default Default value if setting not found
     * @param string $group Setting group
     * @return mixed Setting value
     */
    function get_setting($name, $default = null, $group = 'general')
    {
        try {
            $setting = \Illuminate\Support\Facades\DB::table('settings')
                ->where('group', $group)
                ->where('name', $name)
                ->first();
            
            if ($setting) {
                $payload = json_decode($setting->payload, true);
                return $payload['value'] ?? $default;
            }
            
            return $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('get_company_logo')) {
    /**
     * Get company logo URL
     * 
     * @return string|null Logo URL or null if not set
     */
    function get_company_logo()
    {
        $logo = get_setting('company_logo');
        
        if ($logo) {
            // If it's already a full URL, return it
            if (filter_var($logo, FILTER_VALIDATE_URL)) {
                return $logo;
            }
            
            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($logo);
            
            // If the URL doesn't start with http, it might be a relative path due to misconfigured APP_URL
            if (!str_starts_with($url, 'http')) {
                return asset('storage/' . $logo);
            }
            
            return $url;
        }
        
        return null;
    }
}

if (!function_exists('get_company_name')) {
    /**
     * Get company name from settings
     * 
     * @return string Company name
     */
    function get_company_name()
    {
        return get_setting('company_name', config('app.name', 'CRM System'));
    }
}

if (!function_exists('get_company_info')) {
    /**
     * Get all company information
     * 
     * @return array Company information
     */
    function get_company_info()
    {
        return [
            'name' => get_setting('company_name', config('app.name')),
            'email' => get_setting('company_email', 'hello@example.com'),
            'phone' => get_setting('company_phone', '+1234567890'),
            'address' => get_setting('company_address', ''),
            'tax_id' => get_setting('tax_id', ''),
            'logo' => get_company_logo(),
        ];
    }
}
