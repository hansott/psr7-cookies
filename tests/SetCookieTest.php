<?php

namespace HansOtt\PSR7Cookies;

use GuzzleHttp\Psr7\Response;

final class SetCookieTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_gets_and_sets()
    {
        $cookie = new SetCookie('name', 'value');
        $responseWithCookie = $cookie->addToResponse(new Response());
        $httpResponse = \GuzzleHttp\Psr7\str($responseWithCookie);
        $expected = 'HTTP/1.1 200 OK'."\n";
        $expected .= 'Set-Cookie: name=value'."\r\n\r\n";
        // $this->assertEquals($expected, $httpResponse);
    }

    public function test_it_converts_to_header_value()
    {
        $cookie = new SetCookie('name', 'value');
        $this->assertEquals('name=value', $cookie->toHeaderValue());

        $cookie = new SetCookie('NaMe', 'value', 0);
        $this->assertEquals('NaMe=value', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value with space');
        $this->assertEquals('name=value+with+space', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0,  '/path/');
        $this->assertEquals('name=value; path=/path/', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0,  '/path/', 'domain.tld');
        $this->assertEquals('name=value; path=/path/; domain=domain.tld', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0,  '/path/', 'domain.tld', true);
        $this->assertEquals('name=value; path=/path/; domain=domain.tld; secure', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 0,  '/path/', 'domain.tld', true, true);
        $this->assertEquals('name=value; path=/path/; domain=domain.tld; secure; httponly', $cookie->toHeaderValue());

        $cookie = new SetCookie('name', 'value', 1466459967, '', '', true, true);
        $this->assertEquals('name=value; expires=Mon, 20-Jun-2016 21:59:27 GMT; secure; httponly', $cookie->toHeaderValue());

        $cookie = SetCookie::thatDeletesCookie('name');
        $expected = sprintf('name=deleted; expires=%s', gmdate('D, d-M-Y H:i:s T', 1));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        $now = new \DateTimeImmutable();
        $cookie = SetCookie::thatExpires('name', 'value', $now);
        $timestamp = (int) $now->format('U');
        $expected = sprintf('name=value; expires=%s', gmdate('D, d-M-Y H:i:s T', $timestamp));
        $this->assertEquals($expected, $cookie->toHeaderValue());

        $cookie = SetCookie::thatStaysForever('name', 'value', '/path/', 'domain.tld');
        $expiresInFiveYear = time() + 5 * 365 * 3600 * 24;
        $expected = sprintf('name=value; expires=%s; path=/path/; domain=domain.tld', gmdate('D, d-M-Y H:i:s T', $expiresInFiveYear));
        $this->assertEquals($expected, $cookie->toHeaderValue());
    }
}
