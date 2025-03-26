<?php

namespace App\Traits;

trait EnumHelper
{
    public static function values(): array
    {
        if (method_exists(__CLASS__, 'cases') === true) {
            $cases = self::cases();

            return array_column($cases, 'value');
        }

        return [];
    }
}
