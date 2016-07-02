<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies\Signer\Hmac;

use HansOtt\PSR7Cookies\Signer\Hmac;

final class Sha256 extends Hmac
{
    public function getHashAlgorithm() : string
    {
        return 'sha256';
    }

    public function getHashLength() : int
    {
        return 64;
    }
}
