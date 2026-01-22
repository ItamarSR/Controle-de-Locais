<?php

declare(strict_types=1);

namespace Core;

final class Env
{
    public static function getString(string $key, ?string $default = null): ?string
    {
        $v = getenv($key);
        if ($v === false) {
            return $default;
        }
        $v = trim((string)$v);
        return $v === '' ? $default : $v;
    }
}

