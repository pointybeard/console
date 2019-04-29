<?php declare(strict_types=1);

namespace Symphony\Console\Exceptions;

class CommandInvalidException extends ConsoleWithTraceException
{
    public function __construct(string $extension, string $command, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf("The command %s\%s is not an instance of AbstractCommand.", $extension, $command), $code, $previous);
    }
}
