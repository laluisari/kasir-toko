<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format number to Rupiah currency format
     * 
     * @param int|float $amount
     * @return string
     */
    function formatRupiah($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('formatRupiahShort')) {
    /**
     * Format number to Rupiah currency format (short version without Rp prefix)
     * 
     * @param int|float $amount
     * @return string
     */
    function formatRupiahShort($amount)
    {
        return number_format($amount, 0, ',', '.');
    }
}
