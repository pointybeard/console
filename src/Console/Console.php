<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console;

use pointybeard\Helpers\Cli\Message;
use pointybeard\Helpers\Cli\Colour;

class Console extends \Symphony
{
    const EXTENSION_HANDLE = 'console';

    private $args;

    // Declared private to prevent cloning this class with clone operator
    private function __clone()
    {
    }

    // Declared private to prevent unserializing a copy of this class
    private function __wakeup()
    {
    }

    public static function initialise()
    {
        if (self::$_instance instanceof self) {
            throw new Exceptions\ConsoleAlreadyInitialisedException();
        }

        self::$_instance = new self();

        return self::instance();
    }

    public static function instance()
    {
        return self::$_instance;
    }

    protected function __construct()
    {
        try {
            parent::__construct();
        } catch (\Exception $ex) {
            // We want to ignore the 'Headers Already Sent' exception but let
            // everhthing else through. Check the message and rethrow $ex
            // if its not.
            if (!preg_match('@headers\s+already\s+sent@i', $ex->getMessage())) {
                throw $ex;
            }
        }

        // Set Console extension specific handlers
        ExceptionHandler::initialise();
        ErrorHandler::initialise();
    }

    /**
     * Convienence method for displaying an error (red text) and optionally
     * terminating the script.
     */
    public static function displayError(string $message, bool $exit = true): int
    {
        $result = (new Message\Message())
            ->message("ERROR: {$message}")
            ->foreground(Colour\Colour::FG_RED)
            ->display()
        ;

        if (true == $exit) {
            exit(1);
        }

        return $result;
    }

    /**
     * Convienence method for displaying a warning (yellow text).
     */
    public static function displayWarning(string $message): void
    {
        $result = (new Message\Message())
            ->message("WARNING: {$message}")
            ->foreground(Colour\Colour::FG_YELLOW)
            ->flags(Message\Message::FLAG_APPEND_NEWLINE)
            ->display()
        ;
    }
}
