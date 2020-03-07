<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Commands\Console;

use pointybeard\Symphony\Extensions\Console as Console;
use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Foundation\BroadcastAndListen;

class Hello extends Console\AbstractCommand implements BroadcastAndListen\Interfaces\AcceptsListenersInterface
{
    use BroadcastAndListen\Traits\HasListenerTrait;
    use BroadcastAndListen\Traits\HasBroadcasterTrait;

    public function __construct()
    {
        parent::__construct();
        $this
            ->description('echoes all arguments, options, and flags')
            ->version('1.0.2')
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

        (new Message('VERBOSITY'))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;

        (new Message('Change the verbosity level flag (-v, -vv, -vvv) to limit what is shown'))
            ->display()
        ;

        echo PHP_EOL;

        $this->broadcast(
            Symphony::BROADCAST_MESSAGE,
            E_CRITICAL,
            (new Message())
                ->message('CRITICAL (-v or not set at all)')
                ->foreground(Colour::FG_WHITE)
                ->background(Colour::BG_RED),
            STDERR
        );

        $this->broadcast(
            Symphony::BROADCAST_MESSAGE,
            E_ERROR,
            (new Message())
                ->message('ERROR (-v)')
                ->foreground(Colour::FG_RED)
        );

        $this->broadcast(
            Symphony::BROADCAST_MESSAGE,
            E_WARNING,
            (new Message())
                ->message('WARNING (-vv)')
                ->foreground(Colour::FG_YELLOW)
        );

        $this->broadcast(
            Symphony::BROADCAST_MESSAGE,
            E_NOTICE,
            (new Message())
                ->message('NOTICE (-vvv)')
        );

        echo PHP_EOL;

        return true;
    }
}
