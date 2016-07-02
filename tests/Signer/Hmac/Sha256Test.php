<?php

namespace HansOtt\PSR7Cookies\Signer\Hmac;

use HansOtt\PSR7Cookies\Signer;

final class Sha256SignerTest extends AbstractSignerTest
{
    public function getSigner() : Signer
    {
        return new Sha256();
    }
}
