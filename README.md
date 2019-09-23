# ![PSR-7 Cookies](art/psr7-cookies.jpg)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

If you like this package, feel free to [buy me a coffee](https://www.paypal.me/HansOtt/5) ☕️.

## Install

Via Composer

``` bash
$ composer require hansott/psr7-cookies
```

## Usage

### Add cookie to Psr\Http\Message\ResponseInterface

```php
use HansOtt\PSR7Cookies\SetCookie;

// Set a cookie with custom values.
$cookie = new SetCookie('name', 'value', time() + 3600, '/path', 'domain.tld', $secure, $httpOnly, $sameSite);

// Set a cookie to delete a cookie.
$cookie = SetCookie::thatDeletesCookie('name');

// Set a cookie that stays forever (5 years)
$cookie = SetCookie::thatStaysForever('name', 'value');

// Set a cookie that expires at a given time.
$now = new DateTimeImmutable();
$tomorrow = $now->modify('tomorrow');
$cookie = SetCookie::thatExpires('name', 'value', $tomorrow);
// Or:
$cookie = SetCookie::thatExpiresAt('name', 'value', $tomorrow);

// Set a cookie that expires in given seconds.
$cookie = SetCookie::thatExpiresIn('name', 'value', 3600);

// Set a cookie that expires in 7 days using a date string.
$cookie = SetCookie::thatExpiresIn('name', 'value', '7 days');
// Or alternatively:
$cookie = SetCookie::thatExpiresIn('name', 'value', '1 week');

// Set a cookie that expires in a given DateInterval (7 days in the example).
$cookie = SetCookie::thatExpiresIn('name', 'value', new DateInterval('P7D'));
// Or alternatively, but using the date string directly would be shorter:
$cookie = SetCookie::thatExpiresIn('name', 'value', DateInterval::createFromDateString('7 days'));
$cookie = SetCookie::thatExpiresIn('name', 'value', DateInterval::createFromDateString('1 week'));

// Add the cookie to a response
$responseWithCookie = $cookie->addToResponse($response);
```

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email **hansott at hotmail be** instead of using the issue tracker.

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
