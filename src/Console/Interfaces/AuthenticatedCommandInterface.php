<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Interfaces;

interface AuthenticatedCommandInterface
{
    public function authenticate(): void;
}
