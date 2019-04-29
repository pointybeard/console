<?php declare(strict_types=1);

namespace Symphony\Console\Command\Console;

use Symphony\Console as Console;
use Symphony\Console\AbstractInputType as Type;

class Symphony extends Console\AbstractCommand
{
    public function __construct()
    {
        parent::__construct(
            "1.0.0",
            "Runs command provided via extension or workspace/bin",
            "symphony --list" . PHP_EOL .
            "  symphony -t 4141e465 console hello --usage" . PHP_EOL .
            "  symphony -u fred console hello"
        );
    }

    public function usage() : string
    {
        return "Usage: symphony [OPTION]... [EXTENSION] [COMMAND]...";
    }

    public function init() : bool
    {
        parent::init();
        $this
            ->addOption(
                't',
                'token',
                Type::FLAG_OPTIONAL | Type::FLAG_VALUE_REQUIRED,
                "Use token to authenticate before running the command. Note some commands do not require authentication. Check individual command --usage for more info. Cannot set both --token (-t) and --user (-u).",
                function (Console\Input\InputTypeOption $input, Console\AbstractInput $context) {
                    // 1. Make sure that -u | --user isn't also set
                    if ($context->getOption('u') !== null) {
                        throw new Console\Exceptions\ConsoleException("Does not make sense to set both -u (--user) and -t (--token) at the same time.");
                    }

                    // 2. Authenticate with Symphony
                    if (!Console\Console::instance()->isLoggedIn()) {
                        Console\Console::instance()->loginFromToken($context->getOption('t'));
                        if (!Console\Console::instance()->isLoggedIn()) {
                            throw new Console\Exceptions\AuthenticationFailedException("Token provided is not valid");
                        }
                    }

                    return true;
                }
            )
            ->addOption(
                'u',
                'user',
                Type::FLAG_OPTIONAL | Type::FLAG_VALUE_REQUIRED,
                "Will authenticate using this user before running the comment. Password will be prompted for. Note some commands do not require authentication. Check individual command --usage for more info. Cannot set both --token (-t) and --user (-u).",
                function (Console\Input\InputTypeOption $input, Console\AbstractInput $context) {
                    // 1. Make sure that -t | --token isn't also set
                    if ($context->getOption('t') !== null) {
                        throw new Console\Exceptions\ConsoleException("Does not make sense to set both -u (--user) and -t (--token) at the same time.");
                    }

                    // 2. Authenticate with Symphony
                    if (!Console\Console::instance()->isLoggedIn()) {
                        $password = Console\Prompt::display("Enter Password", true);

                        Console\Console::instance()->login($context->getOption('u'), $password);
                        if (!Console\Console::instance()->isLoggedIn()) {
                            throw new Console\Exceptions\ConsoleException("Username and/or password were incorrect.");
                        }
                    }

                    return true;
                }
            )
        ;

        return true;
    }

    public function execute(Console\Interfaces\InputInterface $input) : bool
    {
        // Use $input to figure out what command we are running. Both
        // 'extension' and 'command' will be set
        // Create the command and execute.
        $command = Console\CommandFactory::fetch(
            $input->getArgument('extension'),
            $input->getArgument('command')
        );

        $input->bind(Console\InputCollection::merge(
            $this->inputCollection(),
            $command->inputCollection()
        ));

        return $command->execute($input);
    }
}
