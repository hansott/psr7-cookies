<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use DateTimeInterface;
use Psr\Http\Message\ResponseInterface;

final class SetCookie
{
    private $name;
    private $value;
    private $expiresAt;
    private $path;
    private $domain;
    private $secure;
    private $httpOnly;
    private $sameSite;

    /**
     * SetCookie constructor.
     *
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param int $expiresAt The time the cookie expires
     * @param string $path The path on the server in which the cookie will be available on
     * @param string $domain The domain that the cookie is available to
     * @param bool $secure Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param bool $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param string $sameSite Specifies if the cookie should be send on a cross site request
     *
     * @throws InvalidArgumentException When the cookie name is not valid
     */
    public function __construct(
        string $name,
        string $value,
        int $expiresAt = 0,
        string $path = '',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false,
        string $sameSite = ''
    ) {
        $this->assertValidName($name);
        $this->assertValidSameSite($sameSite);
        $this->name = $name;
        $this->value = $value;
        $this->expiresAt = $expiresAt;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }

    public static function thatDeletesCookie(
        string $name,
        string $path = '',
        string $domain = '',
        $secure = false,
        $httpOnly = false,
        string $sameSite = ''
    ) : SetCookie {
        return new static($name, 'deleted', 1, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    public static function thatExpires(
        string $name,
        string $value,
        DateTimeInterface $expiresAt,
        string $path = '',
        string $domain = '',
        $secure = false,
        $httpOnly = false,
        string $sameSite = ''
    ) : SetCookie {
        $expiresAt = (int) $expiresAt->format('U');

        return new static($name, $value, $expiresAt, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    public static function thatStaysForever(
        string $name,
        string $value,
        string $path = '',
        string $domain = '',
        $secure = false,
        $httpOnly = false,
        string $sameSite = ''
    ) : SetCookie {
        $expiresInFiveYear = time() + 5 * 365 * 3600 * 24;

        return new static($name, $value, $expiresInFiveYear, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    private function assertValidName(string $name)
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters.', $name)
            );
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }
    }

    private function assertValidSameSite(string $sameSite)
    {
        if (!in_array($sameSite, ['', 'lax', 'strict'])) {
            throw new InvalidArgumentException('The same site attribute must be "lax", "strict" or ""');
        }
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function expiresAt() : int
    {
        return $this->expiresAt;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getDomain() : string
    {
        return $this->domain;
    }

    public function isSecure() : bool
    {
        return $this->secure;
    }

    public function isHttpOnly() : bool
    {
        return $this->httpOnly;
    }

    public function getSameSite() : string
    {
        return $this->sameSite;
    }

    public function toHeaderValue() : string
    {
        $headerValue = sprintf('%s=%s', $this->name, urlencode($this->value));

        if ($this->expiresAt !== 0) {
            $headerValue .= sprintf(
                '; expires=%s',
                gmdate('D, d-M-Y H:i:s T', $this->expiresAt)
            );
        }

        if (empty($this->path) === false) {
            $headerValue .= sprintf('; path=%s', $this->path);
        }

        if (empty($this->domain) === false) {
            $headerValue .= sprintf('; domain=%s', $this->domain);
        }

        if ($this->secure) {
            $headerValue .= '; secure';
        }

        if ($this->httpOnly) {
            $headerValue .= '; httponly';
        }

        if ($this->sameSite != '') {
            $headerValue .= sprintf('; samesite=%s', $this->sameSite);
        }

        return $headerValue;
    }

    public function addToResponse(ResponseInterface $response) : ResponseInterface
    {
        return $response->withAddedHeader('Set-Cookie', $this->toHeaderValue());
    }
}
