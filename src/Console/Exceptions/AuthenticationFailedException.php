<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Exceptions;

class AuthenticationFailedException extends ConsoleException
{
    public function __construct(string $message = 'Command requires authentication. Use --user or --token.', $code = 0, \Exception $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
