<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies;

use HansOtt\PSR7Cookies\Signer\Key;

interface Signer
{
    /**
     * Sign a cookie.
     *
     * @param SetCookie $setCookie
     * @param Key $key
     *
     * @return SetCookie
     */
    public function sign(SetCookie $setCookie, Key $key) : SetCookie;

    /**
     * Verify a cookie.
     *
     * @param Cookie $cookie
     * @param Key $key
     *
     * @return Cookie
     */
    public function verify(Cookie $cookie, Key $key) : Cookie;
}
