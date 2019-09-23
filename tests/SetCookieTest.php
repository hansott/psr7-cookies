<?php

namespace HansOtt\PSR7Cookies;

use DateInterval;
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
        $httpResponse = str($responseWithCookie);
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

        $expireTS = time();
        $now = new DateTimeImmutable("@{$expireTS}");
        $cookie = SetCookie::thatExpiresAt('name', 'value', $now);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test positive seconds interval
        $expireSecs = 123;
        $expireTS = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireSecs);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test negative seconds interval
        $expireSecs = -123;
        $expireTS = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireSecs);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test positive string expression interval
        $expireStr = '1 day';
        $expireSecs = 1 * 86400;
        $expireTS = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireStr);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test negative string expression interval
        $expireStr = '-1 day';
        $expireSecs = -1 * 86400;
        $expireTS = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireStr);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test 0 string expression interval
        $expireStr = '0 day';
        $expireSecs = 0 * 86400;
        $expireTS = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireStr);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test positive DateInterval
        $expireStr = '1 day';
        $expireSecs = 1 * 86400;
        $expireTS = time() + $expireSecs;
        $expireIn = DateInterval::createFromDateString($expireStr);
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireIn);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test negative DateInterval
        $expireStr = '-1 day';
        $expireSecs = -1 * 86400;
        $expireTS = time() + $expireSecs;
        $expireIn = DateInterval::createFromDateString($expireStr);
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireIn);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test empty DateInterval
        $expireIn = new DateInterval('PT0S');
        $expireSecs = 0;
        $expireTS = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireIn);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $expireTS));
        $this->assertEquals($expected, $cookie->toHeaderValue());
    }

    /**
     * @dataProvider invalidExpiredInProvider
     */
    public function test_that_expires_in_raises_exception_for_invalid_values($expiredIn)
    {
        $this->expectException(InvalidArgumentException::class);
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expiredIn);
    }

    public function invalidExpiredInProvider()
    {
        return [
            [null],
            [true],
            [false],
            [1.23],
            [new \stdClass],
            ['not-a-valid-interval'],
        ];
    }
}
