<?php

declare(strict_types=1);

namespace Symphony\Console\Exceptions;

use Symphony\Console as Console;

class RequiredInputMissingException extends ConsoleException
{
    private $input;

    public function __construct(Console\AbstractInputType $input, $code = 0, \Exception $previous = null)
    {
        $this->input = $input;

        return parent::__construct(sprintf(
            'missing %s %s%s',
            $input->getType(),
            'option' == $input->getType() ? '-' : '',
            'option' == $input->getType() ? $input->name() : strtoupper($input->name())
        ), $code, $previous);
    }

    public function getInput(): Console\AbstractInputType
    {
        return $this->input;
    }
}
