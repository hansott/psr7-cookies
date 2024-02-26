# ![PSR-7 Cookies](art/psr7-cookies.jpg)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Supported PHP versions][ico-php-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

## Install

Via Composer

``` bash
$ composer require hansott/psr7-cookies
```

## Usage

### Add cookie to Psr\Http\Message\ResponseInterface

```php
<?php

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

If you discover any security related issues, please email **hans at iott consulting** instead of using the issue tracker.

## Credits

- [Hans Ott][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hansott/psr7-cookies.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hansott/psr7-cookies.svg?style=flat-square
[ico-php-version]: https://img.shields.io/packagist/php-v/hansott/psr7-cookies.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/hansott/psr7-cookies
[link-downloads]: https://packagist.org/packages/hansott/psr7-cookies
[link-author]: https://github.com/hansott
[link-contributors]: ../../contributors
