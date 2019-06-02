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
            ->description('echoes all arguments, options, and flags')
            ->version('1.0.1')
            ->example(
                'symphony console hello -a 123 --bravo=456'
            )
            ->support("If you believe you have found a bug, please report it using the GitHub issue tracker at https://github.com/pointybeard/console/issues, or better yet, fork the library and submit a pull request.\r\n\r\nCopyright 2015-2019 Alannah Kearney. See ".realpath(__DIR__.'/../LICENCE')." for software licence information.\r\n")
            ->bindFlags(Input\AbstractInputHandler::FLAG_VALIDATION_SKIP_UNRECOGNISED)
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

        $position = 0;
        foreach ($input->getCollection()->getItemsByType('Argument') as $item) {
            (new Message())
                ->message(sprintf(
                    ' %d: %s => %s',
                    $position + 1,
                    $item->name(),
                    $input->find($item->name)
                ))
                ->display()
            ;
            ++$position;
        }
        echo PHP_EOL;

        (new Message('OPTIONS & FLAGS'))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;

        foreach ($input->getCollection()->getItemsExcludeByType('Argument') as $item) {
            if (null === ($value = $input->find($item->name()))) {
                continue;
            }
            (new Message())
                ->message(sprintf(
                    ' %s => %s',
                    $item->getDisplayName(),
                    Strings\type_sensitive_strval($value)
                ))
                ->display()
            ;
        }

        echo PHP_EOL;

        (new Message('UNRECOGNISED'))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;

        foreach ($input->getInput() as $name => $value) {
            if (null !== $input->getCollection()->find((string) $name)) {
                continue;
            }
            (new Message())
                ->message(sprintf(
                    '%s => %s',
                    is_int($name) ? '[ARGUMENT]' : $name,
                    Strings\type_sensitive_strval($value)
                ))
                ->display()
            ;
        }

        echo PHP_EOL;

        return true;
    }
}
