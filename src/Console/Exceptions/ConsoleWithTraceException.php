<?php

declare(strict_types=1);

namespace Symphony\Console\Exceptions;

use Symphony\Console\Traits;
use Symphony\Console\Interfaces\ReadableTraceExceptionInterface;

class ConsoleWithTraceException extends ConsoleException implements ReadableTraceExceptionInterface
{
    use Traits\hasReadableTraceExceptionTrait;

    public function __construct(string $message, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf(
            "%s\r\n\r\nTrace\r\n==========\r\n%s\r\n",
            $message,
            $this->getReadableTrace()
        ), $code, $previous);
    }
}
