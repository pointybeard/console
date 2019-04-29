<?php declare(strict_types=1);

namespace Symphony\Console\Interfaces;

use Symphony\Console as Console;

interface InputInterface
{
    public function bind(Console\InputCollection $inputCollection) : bool;
}
