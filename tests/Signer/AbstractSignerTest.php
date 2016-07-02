<?php

namespace HansOtt\PSR7Cookies\Signer\Hmac;

use HansOtt\PSR7Cookies\Cookie;
use HansOtt\PSR7Cookies\Signer;
use PHPUnit_Framework_TestCase;
use HansOtt\PSR7Cookies\SetCookie;
use HansOtt\PSR7Cookies\Signer\Key;

abstract class AbstractSignerTest extends PHPUnit_Framework_TestCase
{
    abstract public function getSigner() : Signer;

    private function getCookieName()
    {
        return 'name';
    }

    private function getCookieValue()
    {
        return 'value';
    }

    private function getCookieToSign() : SetCookie
    {
        return new SetCookie(
            $this->getCookieName(),
            $this->getCookieValue()
        );
    }

    private function getExpectedCookie() : Cookie
    {
        return new Cookie(
            $this->getCookieName(),
            $this->getCookieValue()
        );
    }

    private function getKey() : Key
    {
        return new Key('2SOEADLkxbUeMjv7y!pPG@U2R2E4koNXK1Zf!$6lYY#3@2MRY^pfoV82flHmVG8X');
    }

    private function convertSetCookieToCookie(SetCookie $setCookie) : Cookie
    {
        return new Cookie(
            $setCookie->getName(),
            $setCookie->getValue()
        );
    }

    final public function test_it_signs_and_verifies_cookies()
    {
        $signer = $this->getSigner();
        $signedSetCookie = $signer->sign(
            $this->getCookieToSign(),
            $this->getKey()
        );
        $cookie = $this->convertSetCookieToCookie($signedSetCookie);
        $verifiedCookie = $signer->verify($cookie, $this->getKey());

        $this->assertEquals(
            $this->getExpectedCookie(),
            $verifiedCookie
        );
    }

    final public function test_it_detects_tampered_cookies()
    {
        $signer = $this->getSigner();
        $signedSetCookie = $signer->sign(
            $this->getCookieToSign(),
            $this->getKey()
        );
        $cookie = $this->convertSetCookieToCookie($signedSetCookie);
        $tamperedCookie = new Cookie(
            $cookie->getName(),
            $cookie->getValue() . 'tampered!'
        );

        $this->expectException(Signer\Mismatch::class);
        $signer->verify($tamperedCookie, $this->getKey());
    }

    final public function test_mismatch_when_key_changed()
    {
        $signer = $this->getSigner();
        $signedSetCookie = $signer->sign(
            $this->getCookieToSign(),
            $this->getKey()
        );
        $cookie = $this->convertSetCookieToCookie($signedSetCookie);

        $this->expectException(Signer\Mismatch::class);
        $changedKey = new Key(
            $this->getKey()->toString() . 'changed'
        );
        $signer->verify($cookie, $changedKey);
    }
}
