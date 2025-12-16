<?php

namespace App\Traits;

use App\Support\Hashids;
use Illuminate\Database\Eloquent\Model;

trait HashIdTrait
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        $id = $this->getKey();
        
        if (!is_numeric($id)) {
            return $id;
        }

        // Simple Robust Encoding: Base64(ID + Salt)
        // We add a 'salt:' prefix to verify validity
        $string = $id . ':' . substr(md5(class_basename($this) . env('HASH_ID_SALT', 'fixed-salt')), 0, 8);
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        // 1. Try Fallback (Numeric) - Priority for debugging
        if (is_numeric($value)) {
            \Illuminate\Support\Facades\Log::info("HashId: Value '{$value}' is numeric. Using native ID.");
            return $this->where($this->getKeyName(), $value)->first();
        }

        // 2. Decode Logic
        $decodedIdx = $this->decodeHash($value);
        
        if ($decodedIdx) {
            return $this->where($this->getKeyName(), $decodedIdx)->first();
        }

        return null;
    }
    
    /**
     * Override query resolution for Filament
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        // Fallback
        if (is_numeric($value)) {
             return $query->where($this->getKeyName(), $value);
        }

        // Decode
        $decodedIdx = $this->decodeHash($value);
        
        if ($decodedIdx) {
            return $query->where($this->getKeyName(), $decodedIdx);
        }

        return $query->where($this->getKeyName(), -1);
    }
    
    // Helper to decode new logic
    public function decodeHash($hash) {
        try {
            $base64 = strtr($hash, '-_', '+/');
            $len = strlen($base64);
            $pad = (4 - $len % 4) % 4;
            $base64 .= str_repeat('=', $pad);
            
            $decoded = base64_decode($base64);
            
            // Format is "ID:SALTCHECK"
            if (strpos($decoded, ':') !== false) {
                [$id, $saltCheck] = explode(':', $decoded, 2);
                
                // Verify Salt to prevent tampering
                $expectedSalt = substr(md5(class_basename($this) . env('HASH_ID_SALT', 'fixed-salt')), 0, 8);
                
                if ($saltCheck === $expectedSalt && is_numeric($id)) {
                    return $id;
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
}
