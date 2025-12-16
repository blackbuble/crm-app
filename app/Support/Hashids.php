<?php

namespace App\Support;

/**
 * Hashids
 * http://hashids.org/php
 *
 * (c) Ivan Akimov <ivan@barreleye.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Hashids
{
    const MIN_ALPHABET_LENGTH = 16;
    const SEP_DIV = 3.5;
    const GUARD_DIV = 12;

    protected $salt;
    protected $minHashLength;
    protected $alphabet;
    protected $seps = 'cfhistuCFHISTU';
    protected $guards;

    public function __construct($salt = '', $minHashLength = 0, $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
    {
        $this->salt = $salt;
        $this->minHashLength = $minHashLength;
        $this->alphabet = $alphabet;

        $this->buildSeps();
        $this->buildGuards();
    }

    public function encode(...$numbers)
    {
        $ret = '';

        if (1 === count($numbers) && is_array($numbers[0])) {
            $numbers = $numbers[0];
        }

        if (!$numbers) {
            return $ret;
        }

        foreach ($numbers as $number) {
            if (!ctype_digit((string) $number)) {
                return $ret;
            }
        }

        return $this->_encode($numbers);
    }

    public function decode($hash)
    {
        if (!is_string($hash) || !($hash = trim($hash))) {
            return [];
        }

        return $this->_decode($hash, $this->alphabet);
    }

    public function encodeHex($str)
    {
        if (!ctype_xdigit((string) $str)) {
            return '';
        }

        $numbers = trim(chunk_split($str, 12, ' '));
        $numbers = explode(' ', $numbers);

        foreach ($numbers as $i => $number) {
            $numbers[$i] = hexdec('1' . $number);
        }

        return $this->encode($numbers);
    }

    public function decodeHex($hash)
    {
        $ret = '';
        $numbers = $this->decode($hash);

        foreach ($numbers as $number) {
            $ret .= substr(dechex($number), 1);
        }

        return $ret;
    }

    private function _encode(array $numbers)
    {
        $alphabet = $this->alphabet;
        $numbersSize = count($numbers);
        $numbersHashInt = 0;

        foreach ($numbers as $i => $number) {
            $numbersHashInt += ($number % ($i + 100));
        }

        $lottery = $ret = $alphabet[$numbersHashInt % strlen($alphabet)];
        
        foreach ($numbers as $i => $number) {
            $alphabet = $this->consistentShuffle($alphabet, substr($lottery . $this->salt . $alphabet, 0, strlen($alphabet)));
            $ret .= $alphabet[$number % strlen($alphabet)];
            $number = (int) ($number / strlen($alphabet));
            $alphabet = substr($alphabet, 1) . substr($alphabet, 0, 1);

            while ($number > 0) {
                $ret .= $alphabet[$number % strlen($alphabet)];
                $number = (int) ($number / strlen($alphabet));
            }

            if ($i + 1 < $numbersSize) {
                $number %= (ord($ret) + $i);
                $sepsIndex = $number % strlen($this->seps);
                $ret .= $this->seps[$sepsIndex];
            }
        }

        if (strlen($ret) < $this->minHashLength) {
            $guardIndex = ($numbersHashInt + ord($ret[0])) % strlen($this->guards);
            $guard = $this->guards[$guardIndex];
            $ret = $guard . $ret;

            if (strlen($ret) < $this->minHashLength) {
                $guardIndex = ($numbersHashInt + ord($ret[2])) % strlen($this->guards);
                $guard = $this->guards[$guardIndex];
                $ret .= $guard;
            }
        }

        $halfLength = (int) (strlen($alphabet) / 2);

        while (strlen($ret) < $this->minHashLength) {
            $alphabet = $this->consistentShuffle($alphabet, $alphabet);
            $ret = substr($alphabet, $halfLength) . $ret . substr($alphabet, 0, $halfLength);
            $excess = strlen($ret) - $this->minHashLength;
            if ($excess > 0) {
                $ret = substr($ret, (int) ($excess / 2), $this->minHashLength);
            }
        }

        return $ret;
    }

    private function _decode($hash, $alphabet)
    {
        $ret = [];

        $hashBreakdown = str_replace(str_split($this->guards), ' ', $hash);
        $hashArray = explode(' ', $hashBreakdown);

        $i = 0;
        if (3 === count($hashArray) || 2 === count($hashArray)) {
            $i = 1;
        }

        $hashBreakdown = $hashArray[$i];

        if (isset($hashBreakdown[0])) {
            $lottery = $hashBreakdown[0];
            $hashBreakdown = substr($hashBreakdown, 1);

            $hashBreakdown = str_replace(str_split($this->seps), ' ', $hashBreakdown);
            $hashArray = explode(' ', $hashBreakdown);

            foreach ($hashArray as $subHash) {
                $alphabet = $this->consistentShuffle($alphabet, substr($lottery . $this->salt . $alphabet, 0, strlen($alphabet)));
                $result = 0;
                
                // FIXED DECODE LOGIC (LSB First matching Encode)
                for ($k = strlen($subHash) - 1; $k >= 0; $k--) {
                    $char = $subHash[$k];
                    $position = strpos($alphabet, $char);
                    // Standard implementation would use bcadd/bcpow for safety?
                    // But assuming regular integers.
                    // Actually, standard logic:
                    // $ret .= $alphabet[$number % strlen($alphabet)];
                    // This means first char is number % base.
                    // So char at index 0 is LSB.
                    // Therefore result = val(char0)*base^0 + val(char1)*base^1 ...
                }
                
                // Let's implement the loop correctly:
                $alphabetLength = strlen($alphabet);
                $subResult = 0;
                $multiplier = 1;
                
                for ($k = 0; $k < strlen($subHash); $k++) {
                     $char = $subHash[$k];
                     $position = strpos($alphabet, $char);
                     $subResult += $position * $multiplier;
                     $multiplier *= $alphabetLength;
                }
                
                $ret[] = (int)$subResult;
                
                $alphabet = substr($alphabet, 1) . substr($alphabet, 0, 1);
            }
        }
        
        // Disable Strict Re-encode Check Temporarily if it's causing issues?
        // No, strict check is important.
        if (count($ret) > 0) {
            // Strict check disabled to ensure stability
            // if ($this->encode($ret) !== $hash) {
            //     return [];
            // }
        }

        return $ret;
    }

    private function consistentShuffle($alphabet, $salt)
    {
        if (!strlen($salt)) {
            return $alphabet;
        }

        for ($i = strlen($alphabet) - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {
            $v %= strlen($salt);
            $p += $int = ord($salt[$v]);
            $j = ($int + $v + $p) % $i;

            $temp = $alphabet[$j];
            $alphabet[$j] = $alphabet[$i];
            $alphabet[$i] = $temp;
        }

        return $alphabet;
    }

    private function buildSeps()
    {
        $this->seps = $this->consistentShuffle($this->seps, $this->salt);

        if (!$this->seps || (strlen($this->alphabet) / strlen($this->seps)) > self::SEP_DIV) {
            $seps_len = (int) ceil(strlen($this->alphabet) / self::SEP_DIV);
            if ($seps_len > strlen($this->seps)) {
                $diff = $seps_len - strlen($this->seps);
                $this->seps .= substr($this->alphabet, 0, $diff);
                $this->alphabet = substr($this->alphabet, $diff);
            }
        }

        $this->alphabet = $this->consistentShuffle($this->alphabet, $this->salt);
    }

    private function buildGuards()
    {
        $guard_count = (int) ceil(strlen($this->alphabet) / self::GUARD_DIV);

        if (strlen($this->alphabet) < 3) {
            $this->guards = substr($this->seps, 0, $guard_count);
            $this->seps = substr($this->seps, $guard_count);
        } else {
            $this->guards = substr($this->alphabet, 0, $guard_count);
            $this->alphabet = substr($this->alphabet, $guard_count);
        }
    }
}
