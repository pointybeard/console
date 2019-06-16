<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Exceptions;

class UnableToLoadCommandException extends ConsoleException
{
    public function __construct(string $extension, string $command, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf("The command %s\%s could not be loaded. Returned: %s", $extension, $command, $previous->getMessage()), $code, $previous);
    }
}
