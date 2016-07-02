<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies\Signer\Hmac;

use HansOtt\PSR7Cookies\Signer\Hmac;

final class Sha512 extends Hmac
{
    final public function getHashAlgorithm() : string
    {
        return 'sha512';
    }

    final public function getHashLength() : int
    {
        return 128;
    }
}
