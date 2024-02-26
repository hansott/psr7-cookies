<?php

namespace HansOtt\PSR7Cookies;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use function gmdate;
use function sprintf;
use function time;

final class SetCookieTest extends TestCase
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
        $setCookie = new SetCookie('name', 'value', $expiresAt, $path, $domain, $secure, $httpOnly, $sameSite);
        $this->assertSame($expiresAt, $setCookie->expiresAt());
        $this->assertSame($path, $setCookie->getPath());
        $this->assertSame($domain, $setCookie->getDomain());
        $this->assertSame($secure, $setCookie->isSecure());
        $this->assertSame($httpOnly, $setCookie->isHttpOnly());
        $this->assertSame($sameSite, $setCookie->getSameSite());
    }

    public function test_it_can_be_added_to_a_psr_response()
    {
        $cookie = new SetCookie('name', 'value');
        $responseWithCookie = $cookie->addToResponse(new Response());
        $httpResponse = Message::toString($responseWithCookie);
        $expected = 'HTTP/1.1 200 OK'."\r\n";
        $expected .= 'Set-Cookie: name=value'."\r\n\r\n";
        $this->assertEquals($expected, $httpResponse);
    }

    public function test_it_converts_to_header_value()
    {
        $cookie = new SetCookie('name', 'value');
        $this->assertEquals('name=value', $cookie->toHeaderValue());

        $cookie = new SetCookie('NaMe', 'value', 0);
        $this->assertEquals('NaMe=value', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value with space');
        $this->assertEquals('name=value+with+space', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/');
        $this->assertEquals('name=value; path=/path/', $cookie->toHeaderValue());

        $cookie = new SetCookie(' name  ', '   value  ', 0, '   /path/ '); // whitespace
        $this->assertEquals('name=value; path=/path/', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '0'); // falsey path value
        $this->assertEquals('name=value; path=0', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/', 'domain.tld');
        $this->assertEquals('name=value; path=/path/; domain=domain.tld', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/', 'domain.tld', true);
        $this->assertEquals('name=value; path=/path/; domain=domain.tld; secure', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/', 'domain.tld', true, true);
        $this->assertEquals('name=value; path=/path/; domain=domain.tld; secure; httponly', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 1466459967, '', '', true, true);
        $this->assertEquals('name=value; expires=Mon, 20 Jun 2016 21:59:27 GMT; secure; httponly', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0, '/path/', 'domain.tld', true, true, 'strict');
        $this->assertEquals('name=value; path=/path/; domain=domain.tld; secure; httponly; samesite=strict', $cookie->toHeaderValue());

        $cookie = SetCookie::thatDeletesCookie('name');
        $expected = sprintf('name=deleted; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, 1));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        $now = new DateTimeImmutable();
        $cookie = SetCookie::thatExpires('name', 'value', $now);
        $timestamp = (int) $now->format('U');
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $timestamp));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        $cookie = SetCookie::thatStaysForever('name', 'value', '/path/', 'domain.tld');
        $expiresInFiveYear = time() + 5 * 365 * 3600 * 24;
        $expected = sprintf('name=value; expires=%s; path=/path/; domain=domain.tld', gmdate(self::$HTTP_DATE_FORMAT, $expiresInFiveYear));
        $this->assertEquals($expected, $cookie->toHeaderValue());
    }

    public function test_it_only_allows_samesite_none_with_secure()
    {
        $cookie = new SetCookie('name', 'value', time(), '/', '', true, true, 'none');
        $this->assertEquals('none', $cookie->getSameSite());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The same site attribute can only be \"none\" when secure is set to true");
        new SetCookie('name', 'value', time(), '/', '', false, true, 'none');
    }
}
