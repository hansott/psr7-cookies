<?php

namespace HansOtt\PSR7Cookies;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Psr7\ServerRequest;

final class RequestCookiesTest extends PHPUnit_Framework_TestCase
{
    public function test_it_creates_from_request()
    {
        $request = new ServerRequest('GET', 'https://github.com/guzzle/psr7/blob/master/src/Request.php');
        $request = $request->withCookieParams(['name' => 'value', 'PHPSESSID' => 'abcde123', 1 => 'abcde123']);
        $collection = RequestCookies::createFromRequest($request);
        $expectedCollection = new RequestCookies([
            new Cookie('name', 'value'),
            new Cookie('PHPSESSID', 'abcde123'),
            new Cookie('1', 'abcde123'),
        ]);

        $this->assertEquals($expectedCollection, $collection);
    }

    public function test_it_has_cookie()
    {
        $cookie = new Cookie('name', 'value');
        $cookies = new RequestCookies([$cookie]);
        $this->assertTrue($cookies->has('name'));
        $this->assertTrue($cookies->has('Name'));
        $this->assertTrue($cookies->has('NAME'));
        $this->assertFalse($cookies->has('unknown'));
    }

    public function test_get()
    {
        $sessionCookie = new Cookie('PHPSESSID', 'abc123');
        $cookies = new RequestCookies([$sessionCookie]);
        $cookie = $cookies->get('PHPSESSID');
        $this->assertEquals($sessionCookie, $cookie);
        $this->expectException(CookieNotFound::class);
        $cookies->get('none-existing-cookie');
    }

    public function test_iterate()
    {
        $sessionCookie = new Cookie('PHPSESSID', 'abc123');
        $locale = new Cookie('locale', 'en');
        $cookies = new RequestCookies([$sessionCookie, $locale]);

        $index = 0;
        foreach ($cookies as $name => $cookie) {
            switch ($index) {
                case 0:
                    $this->assertEquals('PHPSESSID', $name);
                    $this->assertEquals($sessionCookie, $cookie);
                    break;
                case 1:
                    $this->assertEquals('locale', $name);
                    $this->assertEquals($locale, $cookie);
                    break;
                default:
                    $this->markTestIncomplete();
            }

            $index++;
        }
    }
}
