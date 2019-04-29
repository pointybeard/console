<?php declare(strict_types=1);

namespace Symphony\Console\Interfaces;

use Symphony\Console as Console;

interface ReadableTraceExceptionInterface
{
    public function getReadableTrace() : ?string;
}
