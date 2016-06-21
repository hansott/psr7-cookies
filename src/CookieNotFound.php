<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use Exception;

final class CookieNotFound extends Exception
{
    public static function forName(string $name) : CookieNotFound
    {
        $message = sprintf('No cookie found with name "%s".', $name);

        return new static($message);
    }
}
