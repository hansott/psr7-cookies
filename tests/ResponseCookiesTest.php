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
                    $sessionCookie->getName() => $sessionCookie->toHeaderValue(),
                    $locale->getName() => $locale->toHeaderValue(),
                )
            ),
            $cookies->addToResponse($response)->getHeaders()
        );
    }
}
