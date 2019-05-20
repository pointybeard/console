<?php

declare(strict_types=1);

namespace Symphony\Console\Commands\Console;

use Symphony\Console as Console;
use Symphony\Console\AbstractInputType as Type;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;
use Symphony;
use AuthorManager;

class Token extends Console\AbstractCommand implements Console\Interfaces\AuthenticatedCommandInterface
{
    use Console\Traits\hasCommandRequiresAuthenticateTrait;

    public function __construct()
    {
        parent::__construct(
            '1.0.0',
            'generates, enables, or disabled author tokens',
            'symphony -t 4141e465 console token -e'.PHP_EOL.
            '  symphony -t 4141e465 console token --author=fred'.PHP_EOL.
            '  symphony -u admin console token -a fred --disable'
        );
    }

    public function init(): bool
    {
        parent::init();
        $this
            ->addOption(
                'a',
                'author',
                Type::FLAG_OPTIONAL | Type::FLAG_VALUE_REQUIRED,
                "Operates on this author. If ommitted, authenticated user is assumed. Changing authors other than your own requires 'Developer' or 'Manager' user type.",
                function (Type $input, Console\AbstractInputHandler $context) {
                    $author = AuthorManager::fetchByUsername($context->getOption('a'));
                    if (!($author instanceof \Author)) {
                        throw new Console\Exceptions\ConsoleException(
                            "User '".$context->getOption('a')."' does not exist."
                        );
                    }

                    return $author;
                },
                null
            )
            ->addOption(
                'e',
                'enable',
                Type::FLAG_OPTIONAL,
                'enables authentication token for author',
                function (Type $input, Console\AbstractInputHandler $context) {
                    // 1. Make sure that -d | --disable isn't also set
                    if (null !== $context->getOption('d')) {
                        throw new Console\Exceptions\ConsoleException('Does not make sense to set both -d (--disable) and -e (--enable) at the same time.');
                    }

                    return true;
                },
                false
            )
            ->addOption(
                'd',
                'disable',
                Type::FLAG_OPTIONAL,
                'disables authentication token for author',
                null,
                false
            )
        ;

        return true;
    }

    public function execute(Console\Interfaces\InputHandlerInterface $input): bool
    {
        $author = $input->getOption('a') instanceof \Author
            ? $input->getOption('a')
            : $input->getOption('u')
        ;

        // Check if the authenticated user has permissions
        if (Symphony::Author()->get('id') != $author->get('id') && !Symphony::Author()->isDeveloper() && !Symphony::Author()->isManager()) {
            throw new Console\Exceptions\AuthenticationFailedException('You must be developer or manager to change that author');
        }

        if (true == $input->getOption('e')) {
            $author->set('auth_token_active', 'yes');
            $author->commit();
            (new Message())
                ->message("SUCCESS: Auth token enabled for user '".$author->get('username')."'")
                ->foreground(Colour::FG_GREEN)
                ->display()
            ;
        } elseif (true == $input->getOption('d')) {
            $author->set('auth_token_active', 'no');
            $author->commit();
            (new Message())
                ->message("SUCCESS: Auth token disabled for user '".$author->get('username')."'")
                ->foreground(Colour::FG_GREEN)
                ->display()
            ;

            // Now that the token is disabled, there is no point continuing
            return true;
        }

        if ('yes' != $author->get('auth_token_active')) {
            (new Message())
                ->message("Auth token is not enabled for author '".$author->get('username')."'. Exiting")
                ->foreground(Colour::FG_YELLOW)
                ->display()
            ;

            return true;
        }

        (new Message())
            ->message("Auth token for '".$author->get('username')."' is: ".Symphony::Author()->createAuthToken())
            ->foreground(Colour::FG_GREEN)
            ->display()
        ;

        return true;
    }
}
