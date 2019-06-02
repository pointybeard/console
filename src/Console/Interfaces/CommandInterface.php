<?php

declare(strict_types=1);

namespace Symphony\Console\Interfaces;

use pointybeard\Helpers\Cli\Input;

interface CommandInterface
{
    public function execute(Input\Interfaces\InputHandlerInterface $input): bool;
    public function init(): void;
}
