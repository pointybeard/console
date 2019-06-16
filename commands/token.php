<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console\Commands\Console;

use pointybeard\Symphony\Extensions\Console as Console;
use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Input\AbstractInputType as Type;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;
use Symphony;
use AuthorManager;

class Token extends Console\AbstractCommand implements Console\Interfaces\AuthenticatedCommandInterface
{
    use Console\Traits\hasCommandRequiresAuthenticateTrait;

    public function __construct()
    {
        parent::__construct();
        $this
            ->description('shows, enables, or disabled author tokens')
            ->version('1.0.2')
            ->example(
                'symphony -t 4141e465 console token enable'.PHP_EOL.
                'symphony -t 4141e465 console token show fred'.PHP_EOL.
                'symphony -u admin console token disable fred'
            )
            ->support("If you believe you have found a bug, please report it using the GitHub issue tracker at https://github.com/pointybeard/console/issues, or better yet, fork the library and submit a pull request.\r\n\r\nCopyright 2015-2019 Alannah Kearney. See ".realpath(__DIR__.'/../LICENCE')." for software licence information.\r\n")
        ;
    }

    public function init(): void
    {
        parent::init();

        $this
            ->addInputToCollection(
                Input\InputTypeFactory::build('Argument')
                    ->name('action')
                    ->flags(Input\AbstractInputType::FLAG_REQUIRED)
                    ->description('can be enable, disable, or show')
                    ->validator(
                        function (Type $input, Input\AbstractInputHandler $context) {
                            $action = strtolower($context->find('action'));
                            if (!in_array($action, ['show', 'enable', 'disable'])) {
                                throw new Console\Exceptions\ConsoleException('Supported ACTIONs are disable and enable.');
                            }

                            return $action;
                        }
                    )
            )
            ->addInputToCollection(
                Input\InputTypeFactory::build('Argument')
                    ->name('author')
                    ->flags(Input\AbstractInputType::FLAG_OPTIONAL)
                    ->description('operates on this author. If ommitted, authenticated user is assumed. Changing authors other than then authenticated author requires \'Developer\' or \'Manager\' privileges')
                    ->validator(
                        function (Type $input, Input\AbstractInputHandler $context) {
                            $author = AuthorManager::fetchByUsername($context->find('author'));
                            if (!($author instanceof \Author)) {
                                throw new Console\Exceptions\ConsoleException(
                                    "Author '".$context->find('author')."' does not exist."
                                );
                            }

                            return $author;
                        }
                    )
            )
        ;
    }

    private function setAuthTokenActive(\Author &$author, bool $value): void
    {
        // Check to see if token status matches $value and if so, return
        if ($author->isTokenActive() == $value) {
            return;
        }
        $author->set('auth_token_active', true === $value ? 'yes' : 'no');
        $author->commit();
    }

    public function usage(): string
    {
        return 'Usage: symphony [OPTIONS]... console token [enable|disable|show] AUTHOR';
    }

    public function execute(Input\Interfaces\InputHandlerInterface $input): bool
    {
        $author = $input->find('author') instanceof \Author
            ? $input->find('author')
            : Symphony::Author()
        ;

        // Check if the authenticated user has permissions
        if (
            Symphony::Author()->get('id') != $author->get('id') &&
            (
                !Symphony::Author()->isDeveloper() ||
                !Symphony::Author()->isManager()
            )
        ) {
            throw new Console\Exceptions\AuthenticationFailedException('Authenticated user must be developer or manager to change that author');
        }

        if ('enable' == $input->find('action') || 'disable' == $input->find('action')) {
            $this->setAuthTokenActive(
                $author,
                'enable' == $input->find('action') ? true : false
            );

            (new Message())
                ->message(sprintf("SUCCESS: Auth token %sd for user '%s'", $input->find('action'), $author->get('username')))
                ->foreground(Colour::FG_GREEN)
                ->display()
            ;
        } elseif ('show' == $input->find('action')) {
            if (!$author->isTokenActive()) {
                (new Message())
                    ->message("Auth token is not enabled for author '".$author->get('username')."'")
                    ->foreground(Colour::FG_YELLOW)
                    ->display()
                ;
            } else {
                (new Message())
                    ->message(sprintf(
                        "Auth token for '%s' is: %s",
                        $author->get('username'),
                        $author->createAuthToken()
                    ))
                    ->foreground(Colour::FG_GREEN)
                    ->display()
                ;
            }
        }

        return true;
    }
}
