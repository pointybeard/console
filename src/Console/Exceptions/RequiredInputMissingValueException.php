<?php declare(strict_types=1);

namespace Symphony\Console\Exceptions;

use Symphony\Console as Console;

class RequiredInputMissingValueException extends ConsoleException
{
    private $input;

    public function __construct(Console\AbstractInputType $input, $code = 0, \Exception $previous = null)
    {
        $this->input = $input;
        return parent::__construct(sprintf(
            "%s %s%s is missing a value",
            $input->getType(),
            $input->getType() == 'option' ? '-' : '',
            $input->getType() == 'option' ? $input->name() : strtoupper($input->name())
        ), $code, $previous);
    }

    public function getInput() : Console\AbstractInputType
    {
        return $this->input;
    }
}
