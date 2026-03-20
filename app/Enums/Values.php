<?php

namespace App\Enums;

trait Values
{
    public static function values(): array
    {
        $result = [];

        foreach (static::cases() as $case) {
            $result[] = $case->value;
        }

        return $result;
    }
}
