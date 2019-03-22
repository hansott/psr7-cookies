<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies\Signer;

use HansOtt\PSR7Cookies\Cookie;
use HansOtt\PSR7Cookies\Signer;
use HansOtt\PSR7Cookies\SetCookie;
use HansOtt\PSR7Cookies\InvalidArgumentException;

abstract class Hmac implements Signer
{
    abstract public function getHashAlgorithm() : string;

    abstract public function getHashLength() : int;

    private function getAlgorithm()
    {
        $algorithm = $this->getHashAlgorithm();
        $this->assertAlgorithmIsSupported($algorithm);

        return $algorithm;
    }

    private function assertAlgorithmIsSupported(string $algorithm)
    {
        $algorithms = hash_algos();

        if (in_array($algorithm, $algorithms, true) === false) {
            throw new InvalidArgumentException(
                sprintf(
                    'The algorithm "%s" is not supported on this system.',
                    $algorithm
                )
            );
        }
    }

    final public function sign(SetCookie $setCookie, Key $key) : SetCookie
    {
        $name = $setCookie->getName();
        $value = $setCookie->getValue();
        $hmac = hash_hmac(
            $this->getAlgorithm(),
            $name.$value,
            $key->toString()
        );

        $value = $hmac . $value;

        return new SetCookie(
            $name,
            $value,
            $setCookie->expiresAt(),
            $setCookie->getPath(),
            $setCookie->getDomain(),
            $setCookie->isSecure(),
            $setCookie->isHttpOnly(),
            $setCookie->getSameSite()
        );
    }

    final public function verify(Cookie $cookie, Key $key) : Cookie
    {
        $name = $cookie->getName();
        $value = $cookie->getValue();
        $hmacGiven = mb_substr($value, 0, $this->getHashLength());
        $originalValue = mb_substr($value, $this->getHashLength());
        $hmac = hash_hmac(
            $this->getAlgorithm(),
            $name.$originalValue,
            $key->toString()
        );

        if (hash_equals($hmac, $hmacGiven) === false) {
            throw new Mismatch();
        }

        return new Cookie(
            $cookie->getName(),
            $originalValue
        );
    }
}
