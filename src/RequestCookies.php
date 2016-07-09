<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use Psr\Http\Message\ServerRequestInterface;

final class RequestCookies implements CookieCollection
{
    /**
     * @var Cookie[]
     */
    private $cookies = [];

    /**
     * RequestCookies constructor.
     *
     * @param Cookie[] $cookies
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $cookies = [])
    {
        $this->guardThatTheseAreCookies($cookies);

        foreach ($cookies as $cookie) {
            $name = $cookie->getName();
            $key = mb_strtolower($name);
            $this->cookies[$key] = $cookie;
        }
    }

    private function guardThatTheseAreCookies(array $cookies)
    {
        foreach ($cookies as $index => $cookie) {
            if (!$cookie instanceof Cookie) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected array of "%s" instances but instead got "%s" at index %d',
                        static::class,
                        is_object($cookie) ? get_class($cookie) : gettype($cookie),
                        $index
                    )
                );
            }
        }
    }

    /**
     * Create instance from a server request.
     *
     * @param ServerRequestInterface $request
     *
     * @return RequestCookies
     */
    public static function createFromRequest(ServerRequestInterface $request) : RequestCookies
    {
        $cookies = [];
        $cookieParams = $request->getCookieParams();
        foreach ($cookieParams as $name => $value) {
            $cookies[] = new Cookie($name, $value);
        }

        return new static($cookies);
    }

    /**
     * Does the collection has a cookie with the given name?
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name) : bool
    {
        $key = mb_strtolower($name);

        return isset($this->cookies[$key]);
    }

    /**
     * Get a cookie from the collection by name.
     *
     * @param string $name
     *
     * @throws CookieNotFound
     *
     * @return Cookie
     */
    public function get(string $name) : Cookie
    {
        $key = mb_strtolower($name);

        if (isset($this->cookies[$key]) === false) {
            throw CookieNotFound::forName($name);
        }

        return $this->cookies[$key];
    }

    public function current() : Cookie
    {
        return current($this->cookies);
    }

    public function key() : string
    {
        $key = key($this->cookies);

        if ($key === null) {
            return $key;
        }

        $cookie = $this->cookies[$key];

        return $cookie->getName();
    }

    public function next()
    {
        next($this->cookies);
    }

    public function valid() : bool
    {
        return key($this->cookies) !== null;
    }

    public function rewind()
    {
        reset($this->cookies);
    }
}
