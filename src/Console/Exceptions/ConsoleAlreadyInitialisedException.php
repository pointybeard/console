<?php declare(strict_types=1);

namespace Symphony\Console\Exceptions;

class ConsoleAlreadyInitialisedException extends ConsoleWithTraceException
{
    public function __construct(string $message = "Console has already been initialised. Use Console::instance() instead.", $code = 0, \Exception $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
