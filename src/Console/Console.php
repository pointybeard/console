<?php

declare(strict_types=1);

namespace Symphony\Console;

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
        $result = (new Message())
            ->message("ERROR: {$message}")
            ->foreground(Message::FG_COLOUR_RED)
            ->flags(Message::FLAG_APPEND_NEWLINE)
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
    public static function displayWarning(string $message): int
    {
        $result = (new Message())
            ->message("WARNING: {$message}")
            ->foreground(Message::FG_COLOUR_YELLOW)
            ->flags(Message::FLAG_APPEND_NEWLINE)
            ->display()
        ;
    }
}
