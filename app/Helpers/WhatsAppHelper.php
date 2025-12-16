<?php

if (!function_exists('load_countries_data')) {
    /**
     * Load countries data from JSON file
     * 
     * @return array Array of countries data
     */
    function load_countries_data()
    {
        static $countriesData = null;
        
        if ($countriesData === null) {
            $jsonPath = __DIR__ . '/countries.json';
            if (file_exists($jsonPath)) {
                $json = file_get_contents($jsonPath);
                $data = json_decode($json, true);
                $countriesData = $data['countries'] ?? [];
            } else {
                $countriesData = [];
            }
        }
        
        return $countriesData;
    }
}

if (!function_exists('format_whatsapp_number')) {
    /**
     * Format phone number for WhatsApp with dynamic country code
     * 
     * @param string $phone The phone number to format
     * @param string|null $countryCode The country code (e.g., '+62', '+1', '+44')
     * @return string|null Formatted WhatsApp number or null if phone is empty
     */
    function format_whatsapp_number($phone, $countryCode = null)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If no country code provided, default to Indonesia (+62)
        if (empty($countryCode)) {
            $countryCode = '+62';
        }
        
        // Remove the '+' sign from country code for processing
        $countryCodeNumber = ltrim($countryCode, '+');
        
        // If phone starts with 0, remove it (local format)
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // If phone already starts with country code, remove it
        if (substr($phone, 0, strlen($countryCodeNumber)) === $countryCodeNumber) {
            $phone = substr($phone, strlen($countryCodeNumber));
        }
        
        // Return formatted number with country code
        return $countryCodeNumber . $phone;
    }
}

if (!function_exists('get_whatsapp_url')) {
    /**
     * Generate WhatsApp URL with formatted number and optional message
     * 
     * @param string $phone The phone number
     * @param string|null $countryCode The country code (e.g., '+62', '+1', '+44')
     * @param string|null $message Optional pre-filled message
     * @return string|null WhatsApp URL or null if phone is empty
     */
    function get_whatsapp_url($phone, $countryCode = null, $message = null)
    {
        $formattedNumber = format_whatsapp_number($phone, $countryCode);
        
        if (!$formattedNumber) {
            return null;
        }
        
        $url = "https://wa.me/{$formattedNumber}";
        
        if (!empty($message)) {
            $url .= '?text=' . urlencode($message);
        }
        
        return $url;
    }
}

if (!function_exists('get_country_codes')) {
    /**
     * Get list of all country codes from JSON data
     * 
     * @return array Array of country codes with country names
     */
    function get_country_codes()
    {
        $countries = load_countries_data();
        $countryCodes = [];
        
        foreach ($countries as $country) {
            $dialCode = $country['dial_code'];
            $name = $country['name'];
            $countryCodes[$dialCode] = "{$name} ({$dialCode})";
        }
        
        // Sort by dial code
        ksort($countryCodes);
        
        return $countryCodes;
    }
}

if (!function_exists('get_countries')) {
    /**
     * Get list of all countries from JSON data
     * 
     * @return array Array of countries
     */
    function get_countries()
    {
        $countries = load_countries_data();
        $countryList = [];
        
        foreach ($countries as $country) {
            $name = $country['name'];
            $countryList[$name] = $name;
        }
        
        // Sort alphabetically
        asort($countryList);
        
        return $countryList;
    }
}

if (!function_exists('get_country_by_name')) {
    /**
     * Get country data by name
     * 
     * @param string $name Country name
     * @return array|null Country data or null if not found
     */
    function get_country_by_name($name)
    {
        $countries = load_countries_data();
        
        foreach ($countries as $country) {
            if ($country['name'] === $name) {
                return $country;
            }
        }
        
        return null;
    }
}

if (!function_exists('get_dial_code_by_country')) {
    /**
     * Get dial code by country name
     * 
     * @param string $countryName Country name
     * @return string|null Dial code or null if not found
     */
    function get_dial_code_by_country($countryName)
    {
        $country = get_country_by_name($countryName);
        return $country ? $country['dial_code'] : null;
    }
}

if (!function_exists('get_country_code_map')) {
    /**
     * Get mapping of country names to dial codes
     * 
     * @return array Array mapping country names to dial codes
     */
    function get_country_code_map()
    {
        $countries = load_countries_data();
        $map = [];
        
        foreach ($countries as $country) {
            $map[$country['name']] = $country['dial_code'];
        }
        
        return $map;
    }
}
