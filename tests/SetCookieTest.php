<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\str;

final class SetCookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The Response cookie rfc date format
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date#Syntax
     * @see https://tools.ietf.org/html/rfc822#section-5
     *
     * @var string Response cookie rfc date format
     */
    private static $HTTP_DATE_FORMAT = 'D, d M Y H:i:s T';

    public function test_getters()
    {
        $expiresAt = time() + 3600;
        $path = '/path';
        $domain = 'domain.tld';
        $secure = true;
        $httpOnly = true;
        $sameSite = 'lax';
        $name = 'me_likey';
        $value = 'cookies';
        $setCookie = new SetCookie($name, $value, $expiresAt, $path, $domain, $secure, $httpOnly, $sameSite);
        self::assertSame($name, $setCookie->getName());
        self::assertSame($name, $setCookie->getName());
        self::assertSame($expiresAt, $setCookie->expiresAt());
        self::assertSame($path, $setCookie->getPath());
        self::assertSame($domain, $setCookie->getDomain());
        self::assertSame($secure, $setCookie->isSecure());
        self::assertSame($httpOnly, $setCookie->isHttpOnly());
        self::assertSame($sameSite, $setCookie->getSameSite());
    }

    public function test_it_can_be_added_to_a_psr_response()
    {
        $cookie = new SetCookie('name', 'value');
        $responseWithCookie = $cookie->addToResponse(new Response());
        $httpResponse = str($responseWithCookie);
        $expected = 'HTTP/1.1 200 OK' . "\r\n";
        $expected .= 'Set-Cookie: name=value' . "\r\n\r\n";
        self::assertEquals($expected, $httpResponse);
    }

    public function test_it_converts_to_header_value()
    {
        $cookie = new SetCookie('name', 'value');
        self::assertEquals('name=value', $cookie->toHeaderValue());

        $cookie = new SetCookie('NaMe', 'value', 0);
        self::assertEquals('NaMe=value', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value with space');
        self::assertEquals('name=value+with+space', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/');
        self::assertEquals('name=value; path=/path/', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/', 'domain.tld');
        self::assertEquals('name=value; path=/path/; domain=domain.tld', $cookie->toHeaderValue());

        $cookie = new SetCookie(
            'name',
            'value',
            0,
            '/path/',
            'domain.tld',
            true
        );
        self::assertEquals('name=value; path=/path/; domain=domain.tld; secure', $cookie->toHeaderValue());

        $cookie = new SetCookie(
            'name',
            'value',
            0,
            '/path/',
            'domain.tld',
            true,
            true
        );
        self::assertEquals(
            'name=value; path=/path/; domain=domain.tld; secure; httponly',
            $cookie->toHeaderValue()
        );

        $cookie = new SetCookie(
            'name',
            'value',
            1466459967,
            '',
            '',
            true,
            true
        );
        self::assertEquals(
            'name=value; expires=Mon, 20 Jun 2016 21:59:27 GMT; secure; httponly',
            $cookie->toHeaderValue()
        );

        $cookie = new SetCookie(
            'name',
            'value',
            0,
            '/path/',
            'domain.tld',
            true,
            true,
            'strict'
        );
        self::assertEquals(
            'name=value; path=/path/; domain=domain.tld; secure; httponly; samesite=strict',
            $cookie->toHeaderValue()
        );

        $cookie = SetCookie::thatDeletesCookie('name');
        $expected = sprintf('name=deleted; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, 1));
        self::assertEquals($expected, $cookie->toHeaderValue());

        $now = new DateTimeImmutable();
        $cookie = SetCookie::thatExpires('name', 'value', $now);
        $timestamp = (int)$now->format('U');
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $timestamp));
        self::assertEquals($expected, $cookie->toHeaderValue());

        $cookie = SetCookie::thatStaysForever('name', 'value', '/path/', 'domain.tld');
        $expiresInFiveYear = time() + 5 * 365 * 3600 * 24;
        $expected = sprintf(
            'name=value; expires=%s; path=/path/; domain=domain.tld',
            gmdate(self::$HTTP_DATE_FORMAT, $expiresInFiveYear)
        );
        self::assertEquals($expected, $cookie->toHeaderValue());
    }

    public function test_throws_exception_when_invalid_name()
    {
        $this->expectException(InvalidArgumentException::class);
        SetCookie::thatStaysForever('', '');
    }

    public function test_throws_exception_when_invalid_same_site()
    {
        $this->expectException(InvalidArgumentException::class);
        SetCookie::thatStaysForever(
            'valid_name',
            'valid_value',
            '',
            '',
            false,
            false,
            'invalid_value'
        );
    }
}
