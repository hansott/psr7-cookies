<?php

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use HansOtt\PSR7Cookies\SetCookie;
use HansOtt\PSR7Cookies\RequestCookies;
use HansOtt\PSR7Cookies\Signer\Hmac\Sha256;
use HansOtt\PSR7Cookies\Signer\Hmac\Sha512;
use HansOtt\PSR7Cookies\Signer\Key;

require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('UTC');

$serverRequest = ServerRequest::fromGlobals();
$requestCookies = RequestCookies::createFromRequest($serverRequest);

$signer = new Sha256();
$key = new Key('LAp27106kAgG14u74t5kb^AYrW4^5ih$');

$counter = 0;
if ($requestCookies->has('counter')) {
    $counterCookie = $requestCookies->get('counter');

    try {
        $counterCookie = $signer->verify($counterCookie, $key);
        $counter = (int) $counterCookie->getValue();
    } catch (\HansOtt\PSR7Cookies\Signer\Mismatch $e) {}

    $counter++;
}

$setCounterCookie = SetCookie::thatStaysForever('counter', $counter);
$setCounterCookie = $signer->sign($setCounterCookie, $key);

$response = new Response();
$body = \GuzzleHttp\Psr7\stream_for(sprintf('Counter: %d', $counter));
$response = $response->withBody($body);
$response = $setCounterCookie->addToResponse($response);

header(sprintf(
    'HTTP/%s %s %s',
    $response->getProtocolVersion(),
    $response->getStatusCode(),
    $response->getReasonPhrase()
));

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
