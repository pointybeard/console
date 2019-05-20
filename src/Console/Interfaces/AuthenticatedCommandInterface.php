<?php

declare(strict_types=1);

namespace Symphony\Console\Interfaces;

interface AuthenticatedCommandInterface
{
    public function authenticate(): void;
}
