<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Commands\Console;

use pointybeard\Symphony\Extensions\Console as Console;
use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Input\AbstractInputType as Type;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Helpers\Cli\Prompt\Prompt;
use pointybeard\Helpers\Foundation\BroadcastAndListen;

class Symphony extends Console\AbstractCommand
{
    public const BROADCAST_MESSAGE = 'broadcast message';

    private $verbosity = null;

    public function __construct()
    {
        parent::__construct();
        $this
            ->description('Runs command provided via extension or workspace')
            ->version('1.0.2')
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

    public function notificationFromCommand($type, ...$arguments): void
    {
        // If the type isnt self::BROADCAST_MESSAGE then return right away
        if (self::BROADCAST_MESSAGE != $type) {
            return;
        }

        // Since this is a BROADCAST_MESSAGE, we expect to get a minimum
        // verbosity trigger level, a message (this can either be a string
        // or an instance of \pointybeard\Helpers\Cli\Message\Message), and
        // finally an optional target output (STDOUT or STDERR generally)

        // Since target is optional, check if it was set by looking at how many
        // items are in $arguments. Anything fewer than 4 means we don't have
        // a target set.
        if (count($arguments) < 4) {
            $arguments[] = null;
        }

        [$initiator, $trigger, $message, $target] = $arguments;

        // Check the message type against the commands verbosity level
        // E.g. (E_NOTICE & E_ERROR) == E_NOTICE
        if (!(($trigger & $this->verbosity) == $trigger)) {
            return;
        }

        // Assume that, if target isn't set, that E_ERROR should go to
        // STDERR instead of STDOUT
        if ((E_ERROR & E_CRITICAL) == $trigger && null == $target) {
            $target = STDERR;
        } elseif (null == $target) {
            $target = STDOUT;
        }

        // Message object has come through
        if (!($message instanceof Message)) {
            $message = (new Message($message));
        }

        $message->display($target);
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

        // Add a listener so we can display messages if the command broadcasts
        if ($command instanceof BroadcastAndListen\Interfaces\AcceptsListenersInterface) {
            $command->addListener([$this, 'notificationFromCommand']);
        }

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

        // Set the internal $verbosity flag as this is needed in the
        // notificationFromCommand() broadcast listener
        $this->verbosity = $input->find('v');

        if ($command instanceof Console\Interfaces\AuthenticatedCommandInterface) {
            $command->authenticate();
        }

        return $command->execute($input);
    }
}
