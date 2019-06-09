<?php

declare(strict_types=1);

namespace Symphony\Console;

use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Functions;
use pointybeard\Helpers\Cli;
use pointybeard\Helpers\Exceptions\ReadableTrace;

abstract class AbstractCommand implements Interfaces\CommandInterface
{
    private $description;
    private $version;
    private $example;
    private $support;

    private $inputCollection;

    private $bindFlags;

    protected function __construct(?string $version = null, ?string $description = null, ?string $example = null, ?string $support = null, ?int $bindFlags = null)
    {
        $this
            ->description($description)
            ->version($version)
            ->example($example)
            ->support($support)
            ->inputCollection(new Input\InputCollection())
            ->bindFlags($bindFlags)
        ;

        static::init();
    }

    // Wrapper for InputCollecton::append() to avoid exposing $inputCollection
    // since only this class should be able to manipulate it (hence why it is
    // private)
    protected function addInputToCollection(Input\Interfaces\InputTypeInterface $input, bool $replaceExisting = false, int $position = Input\InputCollection::POSITION_APPEND)
    {
        $this->inputCollection->add($input, $replaceExisting, $position);

        return $this;
    }

    public function init(): void
    {
        $this
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('help')
                    ->short('h')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('print this help')
                    ->validator(new Input\Validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            (new Cli\Message\Message())
                                ->message((string) $this)
                                ->foreground(Cli\Colour\Colour::FG_GREEN)
                                ->display()
                            ;
                            exit;
                        }
                    ))
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('list')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('shows a list of commands available and exit')
                    ->validator(new Input\Validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            $isExtensionSet = null !== $context->find('extension');

                            $extensions = [];
                            foreach (new CommandIterator() as [$extension, $command]) {
                                $extensions[$extension][] = $command;
                            }

                            if (empty($extensions)) {
                                (new Cli\Message\Message())
                                    ->message('No commands could be found.')
                                    ->foreground(Cli\Colour\Colour::FG_YELLOW)
                                    ->display()
                                ;
                                exit;
                            }

                            (new Cli\Message\Message())
                                ->message(sprintf(
                                    'The following commands are avaialble%s (try `--help` for individual command usage information): ',
                                    $isExtensionSet && 'workspace' != $context->find('extension')
                                        ? ' for extension '.$context->find('extension')
                                        : ''
                                ))
                                ->foreground(Cli\Colour\Colour::FG_GREEN)
                                ->display()
                            ;

                            echo PHP_EOL;

                            foreach ($extensions as $extension => $commands) {
                                if (false == $isExtensionSet || (true == $isExtensionSet && $context->find('extension') == $extension)) {
                                    if (false == $isExtensionSet) {
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
                    ))
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('version')
                    ->short('V')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('display the version of command and exit')
                    ->validator(new Input\Validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            (new Cli\Message\Message())
                                ->message($this->name().' version '.$this->version())
                                ->foreground(Cli\Colour\Colour::FG_GREEN)
                                ->display()
                            ;
                            exit;
                        }
                    ))
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('Argument')
                    ->name('extension')
                    ->flags(Input\AbstractInputType::FLAG_REQUIRED)
                    ->description('name of the extension that contains the command to be run')
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('Argument')
                    ->name('command')
                    ->flags(Input\AbstractInputType::FLAG_REQUIRED)
                    ->description('name of command to run')
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('IncrementingFlag')
                    ->name('v')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_TYPE_INCREMENTING)
                    ->description('verbosity level. -v (errors only), -vv (warnings and errors), -vvv (everything).')
                    ->validator(new Input\Validator(
                        function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                            // Make sure verbosity level never goes above 3
                            return min(3, (int) $context->find('v'));
                        }
                    ))
            )
        ;
    }

    public function __call($name, array $args = [])
    {
        if (!property_exists(self::class, $name)) {
            throw new ReadableTrace\ReadableTraceException("Property '{$name}' does not exist in class ".static::class);
        }

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
            'symphony',
            $this->inputCollection
        );
    }

    public function __toString()
    {
        return Functions\Cli\manpage(
            strtolower($this->name()),
            $this->version(),
            $this->description(),
            $this->inputCollection,
            Cli\Colour\Colour::FG_GREEN,
            Cli\Colour\Colour::FG_WHITE,
            [
                'Examples' => $this->example(),
                'Support' => $this->support(),
            ]
        );
    }
}
