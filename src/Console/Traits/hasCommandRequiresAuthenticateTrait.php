<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Traits;

use pointybeard\Symphony\Extensions\Console as Console;

trait hasCommandRequiresAuthenticateTrait
{
    public function authenticate(): void
    {
        if (!Console\Console::instance()->isLoggedIn()) {
            throw new Console\Exceptions\AuthenticationFailedException();
        }
    }
}
