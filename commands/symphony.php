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
        parent::__construct();
        $this
            ->description('Runs command provided via extension or workspace')
            ->version('1.0.1')
            ->example(
                'symphony --list'.PHP_EOL.
                'symphony -t 4141e465 console hello --usage'.PHP_EOL.
                'symphony -u fred console token -e'
            )
            ->support("If you believe you have found a bug, please report it using the GitHub issue tracker at https://github.com/pointybeard/console/issues, or better yet, fork the library and submit a pull request.\r\n\r\nCopyright 2015-2019 Alannah Kearney. See ".realpath(__DIR__.'/../LICENCE')." for software licence information.\r\n")
            ->bindFlags(Input\AbstractInputHandler::FLAG_VALIDATION_SKIP_UNRECOGNISED)
        ;
    }

    public function usage(): string
    {
        return 'Usage: symphony [OPTION]... EXTENSION [COMMAND]...';
    }

    private static function shouldValidationTrickleThrough(Input\AbstractInputHandler $context): bool
    {
        // Need a way to check if the EXTENSION and COMMAND were set
        // and then who called this. If it wasn't the core 'symphony' command
        // (i.e. no extension or command were set) we should be letting it
        // trickle through to the child command input validation instead
        if (
            null !== $context->find('extension') &&
            null !== $context->find('command') &&
            __CLASS__ == static::class
        ) {
            return true;
        }

        return false;
    }

    public function init(): void
    {
        parent::init();

        $this
            // Help. This will override the help option set in AbstractCommand
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('help')
                    ->short('h')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('print this help')
                    ->validator(new Input\Validator(
                        function (Type $input, Input\AbstractInputHandler $context) {
                            // Allow a child command help message to be displayed
                            if (self::shouldValidationTrickleThrough($context)) {
                                return;
                            }

                            (new Message())
                                ->message((string) $this)
                                ->foreground(Colour::FG_GREEN)
                                ->display()
                            ;
                            exit;
                        }
                    )),
                true
            )
            // Version. This will override the version option set in AbstractCommand
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('version')
                    ->short('V')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('display the version of command and exit')
                    ->validator(new Input\Validator(
                        function (Type $input, Input\AbstractInputHandler $context) {
                            // Allow a child command version to be displayed
                            if (self::shouldValidationTrickleThrough($context)) {
                                return;
                            }

                            (new Message())
                                ->message($this->name().' version '.$this->version())
                                ->foreground(Colour::FG_GREEN)
                                ->display()
                            ;
                            exit;
                        }
                    )),
                true
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('token')
                    ->short('t')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_REQUIRED)
                    ->description('Use token to authenticate before running the command. Note some commands do not require authentication. Check individual command --help for more info. Note that --token and --user cannot both be specified at the same time.')
                    ->validator(new Input\Validator(
                        function (Type $input, Input\AbstractInputHandler $context) {
                            // Make sure that --user (-u) isn't also set
                            if (null !== $context->find('user')) {
                                throw new Console\Exceptions\ConsoleException('Does not make sense to set both --user and --token at the same time.');
                            }

                            // Authenticate with Symphony
                            if (!Console\Console::instance()->isLoggedIn()) {
                                Console\Console::instance()->loginFromToken($context->find('token'));
                                if (!Console\Console::instance()->isLoggedIn()) {
                                    throw new Console\Exceptions\AuthenticationFailedException('Token provided is not valid');
                                }
                            }

                            return true;
                        }
                    ))
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('LongOption')
                    ->name('user')
                    ->short('u')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_REQUIRED)
                    ->description('Will authenticate using this user before running the command. Password will be prompted for. Note some commands do not require authentication. Check individual command --help for more info. Note that --token and --user cannot be both specified at the same time.')
                    ->validator(new Input\Validator(
                        function (Type $input, Input\AbstractInputHandler $context) {
                            // Authenticate with Symphony
                            if (!Console\Console::instance()->isLoggedIn()) {
                                $user = $context->find('user');
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
                    ))
            )
        ;
    }

    public function execute(Input\Interfaces\InputHandlerInterface $input): bool
    {
        // Use $input to figure out what command we are running. Both
        // 'extension' and 'command' will be set
        // Create the command and execute.
        $command = Console\CommandFactory::build(
            $input->find('extension'),
            $input->find('command')
        );

        try {
            // Combine input collections from this command
            // and the subcommand and bind them.
            $input->bind(
                Input\InputCollection::merge(
                    $this->inputCollection(),
                    $command->inputCollection()
                ),
                $command->bindFlags()
            );
        } catch (Input\Exceptions\RequiredInputMissingException | Input\Exceptions\RequiredInputMissingValueException $ex) {
            echo Colour::colourise("symphony: {$ex->getMessage()}", Colour::FG_RED).PHP_EOL.$command->usage().PHP_EOL.PHP_EOL.'Try `-h` for more options.'.PHP_EOL;
            exit(1);
        }

        if ($command instanceof Console\Interfaces\AuthenticatedCommandInterface) {
            $command->authenticate();
        }

        return $command->execute($input);
    }
}
