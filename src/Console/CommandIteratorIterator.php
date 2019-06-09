<?php

declare(strict_types=1);

namespace Symphony\Console;

final class CommandIteratorIterator extends \IteratorIterator
{
    public function __construct()
    {
        parent::__construct(new CommandIterator());
    }

    public function current()
    {
        [$extension, $command] = parent::current();

        return CommandFactory::build($extension, $command);
    }
}
