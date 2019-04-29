<?php declare(strict_types=1);

namespace Symphony\Console\Interfaces;

use Symphony\Console\AbstractInputType as AbstractInputType;
use Symphony\Console\AbstractInput as AbstractInput;

interface InputValidatorInterface
{
    public function validate(AbstractInputType $input, AbstractInput $context);
}
