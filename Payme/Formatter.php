<?php

namespace App\Payme;

class Formatter
{
    /**
     * @param int $money
     * @param int $divide_to
     * @return string
     */
    protected function formatMoney(int $money, int $divide_to = 100): string
    {
        return number_format($money / $divide_to, 2, '.', ',');
    }

    /**
     * @param int $unixTime
     * @param string $format
     * @return false|string
     */
    protected function formatDate(int $unixTime, string $format = "Y/m/d H:i:s")
    {
        return date($format, round($unixTime / 1000));
    }
}