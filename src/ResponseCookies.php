<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use Psr\Http\Message\ResponseInterface;

final class ResponseCookies implements CookieCollection
{
    /**
     * @var SetCookie[]
     */
    private $cookies = [];

    /**
     * ResponseCookies constructor.
     *
     * @param SetCookie[] $cookies
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $cookies)
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
            if (!$cookie instanceof SetCookie) {
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
     * @return SetCookie
     */
    public function get(string $name) : SetCookie
    {
        $key = mb_strtolower($name);

        if (isset($this->cookies[$key]) === false) {
            throw CookieNotFound::forName($name);
        }

        return $this->cookies[$key];
    }

    /**
     * Add the set cookies to a response.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function addToResponse(ResponseInterface $response) : ResponseInterface
    {
        $header = [];
        foreach ($this->cookies as $setCookie) {
            $header[] = $setCookie->toHeaderValue();
        }

        return $response->withAddedHeader('Set-Cookie', $header);
    }

    public function current() : SetCookie
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
