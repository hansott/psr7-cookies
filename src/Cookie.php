<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

final class Cookie
{
    private $name;

    private $value;

    /**
     * Cookie constructor.
     *
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     */
    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get the cookie name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the cookie value.
     *
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }
}
