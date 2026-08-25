<?php

namespace App\Support\Database;

final class LikePattern
{
    public static function contains(string $value): string
    {
        return '%'.str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            $value,
        ).'%';
    }
}
