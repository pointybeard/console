<?php

declare(strict_types=1);

namespace Symphony\Console;

use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Input\AbstractInputType as Type;
use pointybeard\Helpers\Functions;
use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Cli;

abstract class AbstractCommand implements Interfaces\CommandInterface
{
    private $description;
    private $version;
    private $example;
    private $support;

    private $inputCollection;

    const VERBOSITY_LEVEL_0 = 0;
    const VERBOSITY_LEVEL_1 = 1;
    const VERBOSITY_LEVEL_2 = 2;
    const VERBOSITY_LEVEL_3 = 3;

    protected function __construct(string $version = null, string $description = null, string $example = null, string $support = null)
    {
        $this
            ->description($description)
            ->version($version)
            ->example($example)
            ->support($support)
            ->inputCollection(new Input\InputCollection())
        ;

        static::init();
    }

    public function init(): bool
    {
        $this
            ->addOption(
                'h',
                'help',
                Type::FLAG_OPTIONAL,
                'print this help',
                function (Type $input, Input\AbstractInputHandler $context) {
                    (new Cli\Message\Message())
                        ->message((string) $this)
                        ->foreground(Cli\Colour\Colour::FG_GREEN)
                        ->display()
                    ;
                    exit;
                }
            )
            ->addOption(
                'l',
                'list',
                Type::FLAG_OPTIONAL,
                'shows a list of commands available and exit',
                function (Type $input, Input\AbstractInputHandler $context) {
                    $isExtensionSet = null !== $context->getArgument('extension');
                    $commands = CommandAutoloader::fetch();

                    if (empty($commands)) {
                        (new Cli\Message\Message())
                            ->message('No commands could be found.')
                            ->foreground(Cli\Colour\Colour::FG_YELLOW)
                            ->display()
                        ;
                        exit;
                    }

                    (new Cli\Message\Message())
                        ->message(sprintf(
                            'The following commands were located%s: ',
                            $isExtensionSet
                                ? ' for extension '.$context->getArgument('extension')
                                : ''
                        ))
                        ->foreground(Cli\Colour\Colour::FG_GREEN)
                        ->display()
                    ;

                    echo PHP_EOL;

                    foreach (CommandAutoloader::fetch() as $extension => $commands) {
                        if (!$isExtensionSet || ($isExtensionSet && $context->getArgument('extension') == $extension)) {
                            if (!$isExtensionSet) {
                                (new Cli\Message\Message())
                                    ->message("* {$extension}")
                                    ->foreground(Cli\Colour\Colour::FG_GREEN)
                                    ->display()
                                ;
                            }

                            foreach ($commands as $c) {
                                (new Cli\Message\Message())
                                    ->message("  - {$c}")
                                    ->display()
                                ;
                            }

                            echo PHP_EOL;
                        }
                    }
                    exit;
                }
            )
            ->addOption(
                'V',
                'version',
                Type::FLAG_OPTIONAL,
                'display the version of command and exit',
                function (Type $input, Input\AbstractInputHandler $context) {
                    (new Cli\Message\Message())
                        ->message($this->name().' version '.$this->version())
                        ->foreground(Cli\Colour\Colour::FG_GREEN)
                        ->display()
                    ;
                    exit;
                }
            )
            ->addArgument(
                'extension',
                Type::FLAG_REQUIRED,
                'name of the extension that contains the command to be run'
            )
            ->addArgument(
                'command',
                Type::FLAG_REQUIRED,
                'name of command to run.'
            )
            ->addOption(
                'v',
                null,
                Type::FLAG_OPTIONAL | Type::FLAG_TYPE_INCREMENTING,
                'verbosity level. -v (errors only), -vv (warnings and errors), -vvv (everything).',
                null,
                self::VERBOSITY_LEVEL_0
            )
        ;

        return true;
    }

    // public function addArgument(string $name, int $flags = null, string $description = null, object $validator = null, bool $replaceExisting = false): object
    // {
    //     $this->inputCollection->append(new Input\Types\Argument(
    //         $name,
    //         $flags,
    //         $description,
    //         $validator
    //     ), $replaceExisting);
    //
    //     return $this;
    // }
    //
    // public function addOption(string $name, string $long = null, int $flags = null, string $description = null, object $validator = null, $default = false, bool $replaceExisting = false): object
    // {
    //     $this->inputCollection->append(new Input\Types\Option(
    //         $name,
    //         $long,
    //         $flags,
    //         $description,
    //         $validator,
    //         $default
    //     ), $replaceExisting);
    //
    //     return $this;
    // }
    //
    // public function addFlag(string $name, int $flags = null, string $description = null, $default = false): object
    // {
    //     $this->inputCollection->append(new Input\Types\Option(
    //         $name,
    //         null,
    //         $flags,
    //         $description,
    //         null,
    //         $default
    //     ));
    //
    //     return $this;
    // }

    public function __call($name, array $args = [])
    {
        if (empty($args)) {
            return $this->$name;
        }

        $this->$name = $args[0];

        return $this;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function name(): string
    {
        $class = new \ReflectionClass(static::class);

        return $class->getShortName();
    }

    public function extension(): string
    {
        $class = new \ReflectionClass(static::class);

        return array_pop(explode('\\', $class->getNamespaceName()));
    }

    public function usage(): string
    {
        return Functions\Cli\usage(
            'symphony '.strtolower("{$this->extension()} {$this->name()}"),
            $this->inputCollection
        );
    }

    public function __toString()
    {
        return Functions\Cli\manpage(
            $this->name(),
            $this->version(),
            $this->description(),
            $this->inputCollection,
            Colour::FG_GREEN,
            Colour::FG_WHITE,
            [
                'Examples' => $this->example(),
                'Support' => $this->support(),
            ]
        );
    }
}
