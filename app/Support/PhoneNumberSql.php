<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class PhoneNumberSql
{
    public static function numericOnly(string $column = 'no_hp_customer'): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "{$column} ~ '^[0-9]+$'",
            'mysql' => "{$column} REGEXP '^[0-9]+$'",
            default => "{$column} NOT GLOB '*[^0-9]*'",
        };
    }

    public static function validWhatsApp(string $column = 'no_hp_customer'): string
    {
        return "{$column} LIKE '62%' AND LENGTH({$column}) BETWEEN 10 AND 15 AND ".self::numericOnly($column);
    }

    public static function invalidWhatsApp(string $column = 'no_hp_customer'): string
    {
        return 'NOT ('.self::validWhatsApp($column).')';
    }
}
