<?php

namespace App;

class Util
{
    public static function money(float $amount, $currency, $convert = false) {
        return $currency . ' ' . number_format($amount, 2, ',', '.');
    }
}
