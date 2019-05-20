<?php

declare(strict_types=1);

namespace Symphony\Console\Commands\Console;

use Symphony\Console as Console;
use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Input\AbstractInputType as Type;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Helpers\Cli\Prompt\Prompt;

class Symphony extends Console\AbstractCommand
{
    public function __construct()
    {
        parent::__construct(
            '1.0.0',
            'Runs command provided via extension or workspace',
            'symphony --list'.PHP_EOL.
            '  symphony -t 4141e465 console hello --usage'.PHP_EOL.
            '  symphony -u fred console token -e'
        );
    }

    public function usage(): string
    {
        return 'Usage: symphony [OPTION]... EXTENSION [COMMAND]...';
    }

    public function init(): bool
    {
        parent::init();
        $this
            // Help. This will override the help option set in AbstractCommand
            ->addOption(
                'h',
                'help',
                Type::FLAG_OPTIONAL,
                'print this help',
                function (Type $input, Input\AbstractInputHandler $context) {
                    // Need a way to check if the EXTENSION and COMMAND were set
                    // and then who called this. If it was core Symphony command
                    // we should be letting it trickle through to the child
                    // command rather than displaying the Symphony command
                    // help messaage.
                    if (
                        null !== $context->getArgument('extension') &&
                        null !== $context->getArgument('command') &&
                        'Symphony\\Console\\Commands\\Console\\Symphony' == static::class
                    ) {
                        return;
                    }

                    (new Message())
                        ->message((string) $this)
                        ->foreground(Colour::FG_GREEN)
                        ->display()
                    ;
                    exit;
                },
                false,
                true
            )
            // Version. This will override the version option set in AbstractCommand
            ->addOption(
                'V',
                'version',
                Type::FLAG_OPTIONAL,
                'display the version of command and exit',
                function (Type $input, Input\AbstractInputHandler $context) {
                    if (
                        null !== $context->getArgument('extension') &&
                        null !== $context->getArgument('command') &&
                        'Symphony\\Console\\Commands\\Console\\Symphony' == static::class
                    ) {
                        return;
                    }

                    (new Message())
                        ->message($this->name().' version '.$this->version())
                        ->foreground(Colour::FG_GREEN)
                        ->display()
                    ;
                    exit;
                },
                false,
                true
            )
            ->addOption(
                't',
                'token',
                Type::FLAG_OPTIONAL | Type::FLAG_VALUE_REQUIRED,
                'Use token to authenticate before running the command. Note some commands do not require authentication. Check individual command --usage for more info. Cannot set both --token (-t) and --user (-u).',
                function (Type $input, Input\AbstractInputHandler $context) {
                    // 1. Make sure that -u | --user isn't also set
                    if (null !== $context->getOption('u')) {
                        throw new Console\Exceptions\ConsoleException('Does not make sense to set both -u (--user) and -t (--token) at the same time.');
                    }

                    // 2. Authenticate with Symphony
                    if (!Console\Console::instance()->isLoggedIn()) {
                        Console\Console::instance()->loginFromToken($context->getOption('t'));
                        if (!Console\Console::instance()->isLoggedIn()) {
                            throw new Console\Exceptions\AuthenticationFailedException('Token provided is not valid');
                        }
                    }

                    return true;
                }
            )
            ->addOption(
                'u',
                'user',
                Type::FLAG_OPTIONAL | Type::FLAG_VALUE_REQUIRED,
                'Will authenticate using this user before running the command. Password will be prompted for. Note some commands do not require authentication. Check individual command --usage for more info. Cannot set both --token (-t) and --user (-u).',
                function (Type $input, Input\AbstractInputHandler $context) {
                    // Authenticate with Symphony
                    if (!Console\Console::instance()->isLoggedIn()) {
                        $user = $context->getOption('u');
                        $password = (new Prompt('Enter Password'))
                            ->flags(Prompt::FLAG_SILENT)
                            ->validator(function ($input) use ($user) {
                                Console\Console::instance()->login($user, $input);
                                if (!Console\Console::instance()->isLoggedIn()) {
                                    throw new Console\Exceptions\ConsoleException('Username and/or password were incorrect.');
                                }

                                return true;
                            })
                            ->display()
                        ;
                    }

                    return Console\Console::instance()->author();
                }
            )
        ;

        return true;
    }

    public function execute(Input\Interfaces\InputHandlerInterface $input): bool
    {
        // Use $input to figure out what command we are running. Both
        // 'extension' and 'command' will be set
        // Create the command and execute.
        $command = Console\CommandFactory::build(
            $input->getArgument('extension'),
            $input->getArgument('command')
        );

        $input->bind(Input\InputCollection::merge(
            $this->inputCollection(),
            $command->inputCollection()
        ));

        if ($command instanceof Console\Interfaces\AuthenticatedCommandInterface) {
            $command->authenticate();
        }

        return $command->execute($input);
    }
}
