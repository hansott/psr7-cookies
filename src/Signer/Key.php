<?php
declare(strict_types=1);

namespace HansOtt\PSR7Cookies\Signer;

use HansOtt\PSR7Cookies\InvalidArgumentException;

final class Key
{
    private $signKey;

    public function __construct(string $signKey)
    {
        $this->assertSignKeyIsLongEnough($signKey);
        $this->signKey = $signKey;
    }

    private function assertSignKeyIsLongEnough(string $signKey)
    {
        if (mb_strlen($signKey) < 32) {
            throw new InvalidArgumentException(
                'The key should be at least 32 characters long'.
                'and generated using a cryptographically secure pseudo random generator.'
            );
        }
    }

    public function toString()
    {
        return $this->signKey;
    }
}
