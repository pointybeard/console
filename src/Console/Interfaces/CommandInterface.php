<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Interfaces;

use pointybeard\Helpers\Cli\Input;

interface CommandInterface
{
    public function execute(Input\Interfaces\InputHandlerInterface $input): bool;

    public function init(): void;
}
