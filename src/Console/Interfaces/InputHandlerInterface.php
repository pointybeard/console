<?php declare(strict_types=1);

namespace Symphony\Console\Interfaces;

use Symphony\Console as Console;

interface InputHandlerInterface
{
    public function bind(Console\InputCollection $inputCollection, bool $skipValidation=false) : bool;
    public function validate() : void;
    public function getArgument(string $name) : ?string;

    // note that the return value of getOption() isn't always going to be
    // a string like getArgument()
    public function getOption(string $name);

    public function getArguments() : array;
    public function getOptions() : array;
    public function getCollection() : ?Console\InputCollection;
}
