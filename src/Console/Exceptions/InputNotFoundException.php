<?php declare(strict_types=1);

namespace Symphony\Console\Exceptions;

class InputHandlerNotFoundException extends ConsoleWithTraceException
{
    public function __construct(string $handler, string $command, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf("The input handler %s could not be located.", $handler), $code, $previous);
    }
}
