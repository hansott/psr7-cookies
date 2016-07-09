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
            $this->cookies[$cookie->getName()] = $cookie;
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
        return isset($this->cookies[$name]);
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
        if (isset($this->cookies[$name]) === false) {
            throw CookieNotFound::forName($name);
        }

        return $this->cookies[$name];
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
        $setCookies = array_map(function (SetCookie $setCookie) {
            return $setCookie->toHeaderValue();
        }, $this->cookies);

        return $response->withAddedHeader('Set-Cookie', $setCookies);
    }

    public function current() : SetCookie
    {
        return current($this->cookies);
    }

    public function key() : string
    {
        return key($this->cookies);
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
