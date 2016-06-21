<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use Psr\Http\Message\ResponseInterface;

final class ResponseCookieCollection implements CookieCollectionInterface
{
    private $cookies = [];

    /**
     * SetCookieCollection constructor.
     *
     * @param SetCookie[] $cookies
     */
    public function __construct(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->addCookie($cookie);
        }
    }

    public function addCookie(SetCookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
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
