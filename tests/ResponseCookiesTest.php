<?php

namespace HansOtt\PSR7Cookies;

use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

final class ResponseCookiesTest extends PHPUnit_Framework_TestCase
{
    public function test_it_sets_the_headers_on_the_response()
    {
        $sessionCookie = new SetCookie('PHPSESSID', 'abc123');
        $locale = new SetCookie('locale', 'en');
        $cookies = new ResponseCookies([$sessionCookie, $locale]);
        $response = new Response();

        $this->assertEquals(
            array(
                'Set-Cookie' => array(
                    $sessionCookie->toHeaderValue(),
                    $locale->toHeaderValue(),
                )
            ),
            $cookies->addToResponse($response)->getHeaders()
        );
    }

    public function test_has()
    {
        $sessionCookie = new SetCookie('PHPSESSID', 'abc123');
        $cookies = new ResponseCookies([$sessionCookie]);
        $this->assertTrue($cookies->has('PHPSESSID'));
        $this->assertTrue($cookies->has('PHPseSSID'));
        $this->assertTrue($cookies->has('phpsessid'));
        $this->assertFalse($cookies->has('other'));
    }

    public function test_get()
    {
        $sessionCookie = new SetCookie('PHPSESSID', 'abc123');
        $cookies = new ResponseCookies([$sessionCookie]);
        $cookie = $cookies->get('PHPSESSID');
        $this->assertEquals($sessionCookie, $cookie);
        $this->expectException(CookieNotFound::class);
        $cookies->get('none-existing-cookie');
    }

    public function test_iterate()
    {
        $sessionCookie = new SetCookie('PHPSESSID', 'abc123');
        $locale = new SetCookie('locale', 'en');
        $cookies = new ResponseCookies([$sessionCookie, $locale]);

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
