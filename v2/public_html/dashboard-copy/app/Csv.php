<?php
namespace Portal;

final class Csv
{
    /**
     * Neutralize spreadsheet formula injection: a cell that starts with = + - @ tab or CR is
     * executed as a formula by Excel/Sheets. Prefix such values with an apostrophe.
     */
    public static function cell($value): string
    {
        $text = (string) ($value ?? '');
        return ($text !== '' && strpbrk($text[0], "=+-@\t\r") !== false) ? "'" . $text : $text;
    }

    public static function row(array $values): array
    {
        return array_map([self::class, 'cell'], $values);
    }
}
