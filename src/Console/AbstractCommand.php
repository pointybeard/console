<?php declare(strict_types=1);

namespace Symphony\Console;

use Symphony\Console\AbstractInputType as Type;

abstract class AbstractCommand implements Interfaces\CommandInterface
{
    private $description;
    private $version;
    private $help;

    private $inputCollection;

    const VERBOSITY_LEVEL_0 = 0;
    const VERBOSITY_LEVEL_1 = 1;
    const VERBOSITY_LEVEL_2 = 2;
    const VERBOSITY_LEVEL_3 = 3;

    protected function __construct(string $version, string $description, string $help)
    {
        $this->description = $description;
        $this->version = $version;
        $this->help = $help;
        $this->inputCollection = new InputCollection;

        static::init();
    }

    public function init() : bool
    {
        $this
            ->addOption(
                'h',
                'help',
                Type::FLAG_OPTIONAL,
                "print this help",
                function (Type $input, AbstractInput $context) {
                    (new Message)
                        ->message((string)$this)
                        ->foreground(Message::FG_COLOUR_GREEN)
                        ->flags(Message::FLAG_APPEND_NEWLINE)
                        ->display()
                    ;
                    exit;
                }
            )
            ->addOption(
                'l',
                'list',
                Type::FLAG_OPTIONAL,
                "shows a list of commands available and exit",
                function (Type $input, AbstractInput $context) {

                    $isExtensionSet = $context->getArgument('extension') !== null;
                    $commands = CommandAutoloader::fetch();

                    if(empty($commands)) {
                        (new Message)
                            ->message("No commands could be found.")
                            ->flags(Message::FLAG_APPEND_NEWLINE)
                            ->foreground(Message::FG_COLOUR_YELLOW)
                            ->display()
                        ;
                        exit;
                    }

                    (new Message)
                        ->message(sprintf(
                            "The following commands were located%s: ",
                            $isExtensionSet
                                ? " for extension " . $context->getArgument('extension')
                                : ''
                        ))
                        ->flags(Message::FLAG_APPEND_NEWLINE)
                        ->foreground(Message::FG_COLOUR_GREEN)
                        ->display()
                    ;

                    print PHP_EOL;

                    foreach(CommandAutoloader::fetch() as $extension => $commands) {

                        if(!$isExtensionSet || ($isExtensionSet && $context->getArgument('extension') == $extension)) {

                            if(!$isExtensionSet) {
                                (new Message)
                                    ->message("* {$extension}")
                                    ->flags(Message::FLAG_APPEND_NEWLINE)
                                    ->foreground(Message::FG_COLOUR_GREEN)
                                    ->display()
                                ;
                            }

                            foreach($commands as $c) {
                                (new Message)
                                    ->message("  - {$c}")
                                    ->flags(Message::FLAG_APPEND_NEWLINE)
                                    ->display()
                                ;
                            }

                            print PHP_EOL;
                        }

                    }
                    exit;
                }
            )
            ->addOption(
                'V',
                'version',
                Type::FLAG_OPTIONAL,
                "display the version of command and exit",
                function (Type $input, AbstractInput $context) {
                    (new Message)
                        ->message($this->name() . " version " . $this->version())
                        ->foreground(Message::FG_COLOUR_GREEN)
                        ->flags(Message::FLAG_APPEND_NEWLINE)
                        ->display()
                    ;
                    exit;
                }
            )
            ->addArgument(
                'extension',
                Type::FLAG_REQUIRED,
                "name of the extension that contains the command to be run"
            )
            ->addArgument(
                'command',
                Type::FLAG_REQUIRED,
                "name of command to run."
            )
            ->addOption(
                'v',
                null,
                Type::FLAG_OPTIONAL | Type::FLAG_TYPE_INCREMENTING,
                "verbosity level. -v (errors only), -vv (warnings and errors), -vvv (everything).",
                null,
                self::VERBOSITY_LEVEL_0
            )
        ;

        return true;
    }

    public function addArgument(string $name, int $flags = null, string $description = null, object $validator = null, bool $replaceExisting = false) : object
    {
        $this->inputCollection->append(new Input\InputTypeArgument(
            $name,
            $flags,
            $description,
            $validator
        ), $replaceExisting);
        return $this;
    }

    public function addOption(string $name, string $long = null, int $flags = null, string $description = null, object $validator = null, $default = false, bool $replaceExisting = false) : object
    {
        $this->inputCollection->append(new Input\InputTypeOption(
            $name,
            $long,
            $flags,
            $description,
            $validator,
            $default
        ), $replaceExisting);
        return $this;
    }

    public function addFlag(string $name, int $flags = null, string $description = null, $default = false) : object
    {
        $this->inputCollection->append(new Input\InputTypeOption(
            $name,
            null,
            $flags,
            $description,
            null,
            $default
        ));
        return $this;
    }

    public function description() : string
    {
        return $this->description;
    }

    public function version() : string
    {
        return $this->version;
    }

    public function help() : string
    {
        return $this->help;
    }

    public function name() : string
    {
        $class = new \ReflectionClass(static::class);
        return $class->getShortName();
    }

    public function extension() : string
    {
        $class = new \ReflectionClass(static::class);
        return array_pop(explode('\\', $class->getNamespaceName()));
    }

    public function inputCollection() : InputCollection
    {
        return $this->inputCollection;
    }

    public function usage() : string
    {
        $arguments = [];
        foreach ($this->inputCollection->getArguments() as $a) {

            // We don't want the two core arguments to show up since they are
            // are taken caare of a little further down.
            if (in_array($a->name(), ['extension', 'command'])) {
                continue;
            }

            $arguments[] = strtoupper(
                // Wrap with square brackets if it's not required
                is_flag_set(AbstractInputType::FLAG_OPTIONAL, $a->flags()) ||
                !is_flag_set(AbstractInputType::FLAG_REQUIRED, $a->flags())
                    ? "[{$a->name()}]"
                    : $a->name()
            );
        }

        $arguments = trim(implode($arguments, ' '));

        return sprintf(
            "Usage: symphony %s %s [OPTION]... %s%s",
            strtolower($this->extension()),
            strtolower($this->name()),
            $arguments,
            strlen($arguments) > 0 ? '...' : ''
        );
    }

    public function __toString()
    {
        $args = [
            'command' => $this->name(),
            'version' => $this->version(),
            'description' => trim(utf8_wordwrap($this->description())),
            'usage' => $this->usage(),
            'arguments' => [],
            'options' => [],
            'examples' => $this->help()
        ];

        $format = "%s %s, %s
%s

Mandatory values for long options are mandatory for short options too.

Arguments:
  %s

Options:
  %s

Examples:

  %s
        ";

        foreach ($this->inputCollection->getArguments() as $a) {
            $args['arguments'][] = (string)$a;
        }

        foreach ($this->inputCollection->getOptions() as $o) {
            $args['options'][] = (string)$o;
        }

        //var_dump($args['arguments']); die;
        $args['arguments'] = implode($args['arguments'], PHP_EOL . '  ');
        $args['options'] = implode($args['options'], PHP_EOL . '  ');

        return vsprintf($format, $args);
    }
}
