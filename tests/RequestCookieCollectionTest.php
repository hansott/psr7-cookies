<?php

namespace HansOtt\PSR7Cookies;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Psr7\ServerRequest;

final class RequestCookieCollectionTest extends PHPUnit_Framework_TestCase
{
    public function test_it_creates_from_request()
    {
        $request = new ServerRequest('GET', 'https://github.com/guzzle/psr7/blob/master/src/Request.php');
        $request = $request->withCookieParams(['name' => 'value', 'PHPSESSID' => 'abcde123']);
        $collection = RequestCookieCollection::createFromRequest($request);
        $expectedCollection = new RequestCookieCollection([
            new Cookie('name', 'value'),
            new Cookie('PHPSESSID', 'abcde123'),
        ]);

        $this->assertEquals($expectedCollection, $collection);
    }
}
