<?php

declare(strict_types=1);

namespace Symphony\Console\Exceptions;

class AuthenticationFailedException extends ConsoleException
{
    public function __construct(string $message = 'Command requires authentication. Use -u (--user) or -t (--token).', $code = 0, \Exception $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
