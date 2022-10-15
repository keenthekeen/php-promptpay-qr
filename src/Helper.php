<?php

namespace PromptPayQR;

use mermshaus\CRC\CRC16CCITT;

class Helper
{
    public static function formatTarget(string $target): string
    {
        $str = self::sanitizeTarget($target);
        if (strlen($str) >= 13) {
            return $str;
        }

        $str = preg_replace('/^0/', '66', $str);
        $str = '0000000000000'.$str;

        return substr($str, -13);
    }

    /**
     * @param  string[]  $xs
     */
    public static function serialize(array $xs): string
    {
        return implode('', $xs);
    }

    public static function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    public static function f(string $id, string $value): string
    {
        return implode('', [$id, substr('00'.strlen($value), -2), $value]);
    }

    public static function crc16(string $data): string
    {
        $crc16 = new CRC16CCITT();
        $crc16->update($data);
        $checksum = $crc16->finish();

        return strtoupper(bin2hex($checksum));
    }

    public static function sanitizeTarget(string $str): string
    {
        return preg_replace('/[^0-9]/', '', $str) ?? '';
    }
}
