<?php

namespace App\Helpers;

class TotpHelper
{
    /**
     * Generate a secret key (Base32).
     */
    public static function generateSecret(): string
    {
        // Simple random base32 string (A-Z, 2-7)
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Calculate the code for a given secret and timestamp.
     */
    public static function generateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = floor($timestamp / 30);
        
        // Decode Base32 Secret
        $secretKey = self::base32Decode($secret);
        
        // Pack time into binary string (8 bytes, big endian)
        $timePacked = pack('N*', 0) . pack('N*', $timeSlice);
        
        // HMAC-SHA1
        $hash = hash_hmac('sha1', $timePacked, $secretKey, true);
        
        // Dynamic Truncation
        $offset = ord(substr($hash, -1)) & 0xf;
        $binary = (
            ((ord(substr($hash, $offset, 1)) & 0x7f) << 24) |
            ((ord(substr($hash, $offset + 1, 1)) & 0xff) << 16) |
            ((ord(substr($hash, $offset + 2, 1)) & 0xff) << 8) |
            (ord(substr($hash, $offset + 3, 1)) & 0xff)
        );
        
        $otp = $binary % 1000000;
        return str_pad((string)$otp, 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code): bool
    {
        // Check current, prev, and next windows to account for drift
        $ts = time();
        for ($i = -1; $i <= 1; $i++) {
            if (self::generateCode($secret, $ts + ($i * 30)) === $code) {
                return true;
            }
        }
        return false;
    }

    private static function base32Decode(string $base32): string
    {
        $base32 = strtoupper($base32);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        
        foreach (str_split($base32) as $char) {
            $pos = strpos($chars, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        
        $output = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) break;
            $output .= chr(bindec($byte));
        }
        
        return $output;
    }
}
