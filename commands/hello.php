<?php declare(strict_types=1);

namespace Symphony\Console\Commands\Console;

use Symphony\Console as Console;
use Symphony\Console\AbstractInputType as Type;
use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;

class Hello extends Console\AbstractCommand {

    public function __construct() {
        parent::__construct(
            "1.0.0",
            "echoes all arguments.",
            "symphony console hello -a 123 --bravo=456"
        );
    }

    public function execute(Console\Interfaces\InputInterface $input) : bool
    {
        (new Message)
            ->message("Hello! Here are the arguments & options available")
            ->foreground(Colour::FG_WHITE)
            ->background(Colour::BG_BLUE)
            ->display()
        ;

        print PHP_EOL;

        $count = 0;
        (new Message("ARGUMENTS"))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;
        foreach($input->getArguments() as $name => $value) {
            (new Message)
                ->message(sprintf(
                    " %d: %s => %s", $count, $name, $value
                ))
                ->display()
            ;
            $count++;
        }
        print PHP_EOL;

        (new Message("OPTIONS"))
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;

        foreach($input->getOptions() as $name => $value) {

            $o = $input->getCollection()->findOption($name);

            $name = $o instanceof Console\Input\InputTypeOption
                ? $o->name()
                : $name;

            $long = $o instanceof Console\Input\InputTypeOption && $o->long() !== null
                ? $o->long()
                : null;

            $args = [
                strlen($name) > 1 ? '-' : '',
                $name,
                $long !== null ? " (--{$long}) " : ' ',
                Strings\type_sensitive_strval($value)
            ];

            (new Message)
                ->message(vsprintf(
                    " -%s%s%s=> %s", $args
                ))
                ->display()
            ;
        }

        print PHP_EOL;

        return true;
    }
}