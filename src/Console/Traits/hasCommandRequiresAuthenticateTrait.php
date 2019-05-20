<?php

declare(strict_types=1);

namespace Symphony\Console\Traits;

use Symphony\Console as Console;

trait hasCommandRequiresAuthenticateTrait
{
    public function authenticate(): void
    {
        if (!Console\Console::instance()->isLoggedIn()) {
            throw new Console\Exceptions\AuthenticationFailedException();
        }
    }
}
