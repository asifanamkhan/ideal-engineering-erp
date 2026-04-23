<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen'
    ];

    private static $tens = [
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety'
    ];

    private static $thousands = [
        '',
        'Thousand',
        'Million',
        'Billion',
        'Trillion'
    ];

    /**
     * Convert number to words
     *
     * @param float $number
     * @param string $currency
     * @return string
     */
    public static function convert($number, $currency = 'Taka')
    {
        if (!is_numeric($number)) {
            return 'Zero ' . $currency;
        }

        // Handle decimal (cents/paisa)
        $number = round($number, 2);
        $dollars = floor($number);
        $cents = round(($number - $dollars) * 100);

        // Convert dollars
        $words = self::convertNumber($dollars);

        // Add currency
        if ($dollars > 0) {
            $words .= ' ' . $currency;
        } else {
            $words = 'Zero ' . $currency;
        }

        // Add cents if exists
        if ($cents > 0) {
            $words .= ' and ' . self::convertNumber($cents) . ' Paisa';
        } else {
            $words .= ' Only';
        }

        return $words;
    }

    /**
     * Convert number to words (internal method)
     *
     * @param int $number
     * @return string
     */
    private static function convertNumber($number)
    {
        if ($number == 0) {
            return '';
        }

        if ($number < 20) {
            return self::$ones[$number];
        }

        if ($number < 100) {
            $tens = floor($number / 10);
            $ones = $number % 10;

            if ($ones > 0) {
                return self::$tens[$tens] . ' ' . self::$ones[$ones];
            }
            return self::$tens[$tens];
        }

        if ($number < 1000) {
            $hundreds = floor($number / 100);
            $remainder = $number % 100;

            if ($remainder > 0) {
                return self::$ones[$hundreds] . ' Hundred ' . self::convertNumber($remainder);
            }
            return self::$ones[$hundreds] . ' Hundred';
        }

        // For numbers >= 1000
        foreach (self::$thousands as $index => $thousand) {
            $divisor = pow(1000, $index);
            if ($divisor > $number) {
                $prevDivisor = pow(1000, $index - 1);
                $chunk = floor($number / $prevDivisor);
                $remainder = $number % $prevDivisor;

                if ($remainder > 0) {
                    return self::convertNumber($chunk) . ' ' . self::$thousands[$index - 1] . ' ' . self::convertNumber($remainder);
                }
                return self::convertNumber($chunk) . ' ' . self::$thousands[$index - 1];
            }
        }

        return (string) $number;
    }

    /**
     * Convert number to words (Simple version without currency)
     *
     * @param float $number
     * @return string
     */
    public static function simple($number)
    {
        if (!is_numeric($number)) {
            return 'Zero';
        }

        $number = round($number, 2);
        $dollars = floor($number);
        $cents = round(($number - $dollars) * 100);

        $words = self::convertNumber($dollars);

        if ($dollars == 0) {
            $words = 'Zero';
        }

        if ($cents > 0) {
            $words .= ' point ' . self::convertNumber($cents);
        }

        return $words;
    }
}