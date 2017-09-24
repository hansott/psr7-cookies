# PSR-7 Cookies

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

If you like this package, feel free to [donate me a coffee](https://www.paypal.me/HansOtt/5) ☕️!

## Install

Via Composer

``` bash
$ composer require hansott/psr7-cookies
```

## Usage

### Get cookies from Psr\Http\Message\ServerRequestInterface

```php
use HansOtt\PSR7Cookies\RequestCookies;

$serverRequest = ServerRequest::fromGlobals();
$cookies = RequestCookies::createFromRequest($serverRequest);

if ($cookies->has('counter')) {
    $counter = $counter->get('counter');
    $count = $counter->getValue(); // string
}
```

### Add cookie to Psr\Http\Message\ResponseInterface

```php
use HansOtt\PSR7Cookies\SetCookie;

// Set a cookie with custom values.
$cookie = new SetCookie('name', 'value', time() + 3600, '/path', 'domain.tld', $secure, $httpOnly);

// Set a cookie to delete a cookie.
$cookie = SetCookie::thatDeletesCookie('name');

// Set a cookie that stays forever (5 years)
$cookie = SetCookie::thatStaysForever('name', 'value');

// Set a cookie that expires at a given time.
$now = new DateTimeImmutable();
$tomorrow = $now->modify('tomorrow');
$cookie = SetCookie::thatExpires('name', 'value', $tomorrow);

// Add the cookie to a response
$responseWithCookie = $cookie->addToResponse($response);
```

### Sign cookies

```php
use HansOtt\PSR7Cookies\Signer\Hmac\Sha256;
use HansOtt\PSR7Cookies\Signer\Hmac\Sha512;
use HansOtt\PSR7Cookies\Signer\Mismatch;

$signer = new Sha256();
// or
$signer = new Sha512();

// The key should be at least 32 characters long
// and generated using a cryptographically secure pseudo random generator.
$key = new Key(/** ... */);

$counter = SetCookie::thatStaysForever('counter', '10');

// Add the signed cookie to the response
$signedCounter = $signer->sign($counter, $key);

// Get cookie from response
$counter = $cookies->get('counter');

try {
    $counter = $signer->verify($counter, $key);
    $count = $counter->getValue(); // string
} catch (Mismatch $e) {
    // Cookie is tampered!
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email hansott@hotmail.be instead of using the issue tracker.

## Credits

- [Hans Ott][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hansott/psr7-cookies.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/hansott/psr7-cookies/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/hansott/psr7-cookies.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/hansott/psr7-cookies.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hansott/psr7-cookies.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/hansott/psr7-cookies
[link-travis]: https://travis-ci.org/hansott/psr7-cookies
[link-scrutinizer]: https://scrutinizer-ci.com/g/hansott/psr7-cookies/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/hansott/psr7-cookies
[link-downloads]: https://packagist.org/packages/hansott/psr7-cookies
[link-author]: https://github.com/hansott
[link-contributors]: ../../contributors
