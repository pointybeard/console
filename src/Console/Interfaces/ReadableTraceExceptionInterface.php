<?php

declare(strict_types=1);

namespace Symphony\Console\Interfaces;

interface ReadableTraceExceptionInterface
{
    public function getReadableTrace(): ?string;
}
