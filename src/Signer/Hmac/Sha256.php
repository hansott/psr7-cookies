<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies\Signer\Hmac;

use HansOtt\PSR7Cookies\Signer\Hmac;

final class Sha256 extends Hmac
{
    final public function getHashAlgorithm() : string
    {
        return 'sha256';
    }

    final public function getHashLength() : int
    {
        return 64;
    }
}
