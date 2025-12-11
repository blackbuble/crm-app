<?php

if (!function_exists('setting')) {
    function setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('settings');
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                \Illuminate\Support\Facades\Cache::forever('setting.' . $k, $v);
            }
            return true;
        }

        return \Illuminate\Support\Facades\Cache::get('setting.' . $key, $default);
    }
}
