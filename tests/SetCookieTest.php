<?php

namespace HansOtt\PSR7Cookies;

use DateInterval;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

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

        $timestamp = time();
        $now = new DateTimeImmutable("@{$timestamp}");
        $cookie = SetCookie::thatExpiresAt('name', 'value', $now);
        $expected = sprintf('name=value; expires=%s', gmdate(self::$HTTP_DATE_FORMAT, $timestamp));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        // test positive seconds interval
        $expireSecs = 123;
        $excpectedTSmin = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireSecs);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test negative seconds interval
        $expireSecs = -123;
        $excpectedTSmin = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireSecs);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test positive string expression interval
        $expireStr = '1 day';
        $expireSecs = 1 * 86400;
        $excpectedTSmin = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireStr);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test negative string expression interval
        $expireStr = '-1 day';
        $expireSecs = -1 * 86400;
        $excpectedTSmin = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireStr);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test 0 string expression interval
        $expireStr = '0 day';
        $expireSecs = 0 * 86400;
        $excpectedTSmin = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireStr);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test positive DateInterval
        $expireStr = '1 day';
        $expireSecs = 1 * 86400;
        $excpectedTSmin = time() + $expireSecs;
        $expireIn = DateInterval::createFromDateString($expireStr);
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireIn);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test negative DateInterval
        $expireStr = '-1 day';
        $expireSecs = -1 * 86400;
        $excpectedTSmin = time() + $expireSecs;
        $expireIn = DateInterval::createFromDateString($expireStr);
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireIn);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);

        // test empty DateInterval
        $expireIn = new DateInterval('PT0S');
        $expireSecs = 0;
        $excpectedTSmin = time() + $expireSecs;
        $cookie = SetCookie::thatExpiresIn('name', 'value', $expireIn);
        $excpectedTSmax = time() + $expireSecs;
        $actualTS = $this->extractExpireTimestampFrom($cookie->toHeaderValue());
        $this->assertGreaterThanOrEqual($excpectedTSmin, $actualTS);
        $this->assertLessThanOrEqual($excpectedTSmax, $actualTS);
    }

    private function extractExpireTimestampFrom(string $headerValue): int
    {
        static $expireRegex
            = '/'
            . '(Sun|Mon|Tue||Wed|Thu|Fri|Sat)\,'
            . ' [0-3][0-9]'
            . ' (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)'
            . ' [0-9]{4}'
            . ' [0-2][0-9]\:[0-5][0-9]:[0-5][0-9]'
            . ' GMT'
            . '/';

        $expireString = null;

        $parts = explode(';', $headerValue);
        foreach ($parts as $part) {
            $av = explode('=', $part);
            $avName = strtolower(trim($av[0]));
            if ($avName === 'expires') {
                $expireString = $av[1] ?? null;
                break;
            }
        }

        $this->assertNotNull($expireString);
        $this->assertRegExp($expireRegex, $expireString);

        $expireTimestamp = strtotime($expireString);

        $this->assertNotFalse($expireTimestamp);

        return $expireTimestamp;
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

    public function test_it_only_allows_samesite_none_with_secure()
    {
        $cookie = new SetCookie('name', 'value', time(), '/', '', true, true, 'none');
        $this->assertEquals('none', $cookie->getSameSite());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The same site attribute can only be \"none\" when secure is set to true");
        new SetCookie('name', 'value', time(), '/', '', false, true, 'none');
    }
}
