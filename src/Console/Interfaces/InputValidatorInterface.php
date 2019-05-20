<?php

declare(strict_types=1);

namespace Symphony\Console\Interfaces;

use Symphony\Console\AbstractInputType as AbstractInputType;
use Symphony\Console\AbstractInputHandler as AbstractInputHandler;

interface InputValidatorInterface
{
    public function validate(AbstractInputType $input, AbstractInputHandler $context);
}
