<?php

declare(strict_types=1);

namespace Symphony\Console\Commands\Console;

use Symphony\Console as Console;
use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Helpers\Cli\Input;

class Hello extends Console\AbstractCommand
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->description('echoes all arguments')
            ->version('1.0.1')
            ->example(
                'symphony console hello -a 123 --bravo=456'
            )
            ->support("If you believe you have found a bug, please report it using the GitHub issue tracker at https://github.com/pointybeard/console/issues, or better yet, fork the library and submit a pull request.\r\n\r\nCopyright 2015-2019 Alannah Kearney. Use 'symphony -L' to see software licence information.\r\n")
        ;
    }

    public function execute(Input\Interfaces\InputHandlerInterface $input): bool
    {
        (new Message())
            ->message('Hello! Here are the arguments & options available')
            ->foreground(Colour::FG_WHITE)
            ->background(Colour::BG_BLUE)
            ->display()
        ;

        echo PHP_EOL;

        $count = 0;
        (new Message('ARGUMENTS'))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;
        foreach ($input->getArguments() as $name => $value) {
            (new Message())
                ->message(sprintf(
                    ' %d: %s => %s',
                    $count,
                    $name,
                    $value
                ))
                ->display()
            ;
            ++$count;
        }
        echo PHP_EOL;

        (new Message('OPTIONS'))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;

        foreach ($input->getOptions() as $name => $value) {
            $o = $input->getCollection()->findOption($name);

            $name = $o instanceof Input\Types\Option
                ? $o->name()
                : $name;

            $long = $o instanceof Input\Types\Option && null !== $o->long()
                ? $o->long()
                : null;

            $args = [
                strlen($name) > 1 ? '-' : '',
                $name,
                null !== $long ? " (--{$long}) " : ' ',
                Strings\type_sensitive_strval($value),
            ];

            (new Message())
                ->message(vsprintf(
                    ' -%s%s%s=> %s',
                    $args
                ))
                ->display()
            ;
        }

        echo PHP_EOL;

        return true;
    }
}
