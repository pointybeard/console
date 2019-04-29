<?php declare(strict_types=1);

namespace Symphony\Console\Interfaces;

interface CommandInterface
{
    public function execute(InputInterface $input) : bool;
    public function init() : bool;

    public function addArgument(string $name, int $flags = null, string $description = null, object $validator = null, bool $replaceExisting = false) : object;

    public function addOption(string $name, string $long = null, int $flags = null, string $description = null, object $validator = null, $default = null, bool $replaceExisting = false) : object;

    public function addFlag(string $name, int $flags = null, string $description = null, $default = false) : object;
}
