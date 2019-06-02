<?php

declare(strict_types=1);

namespace Symphony\Console;

use pointybeard\Helpers\Functions\Cli;
use pointybeard\Helpers\Cli\Colour\Colour;

class ExceptionHandler
{
    public static $enabled = true;
    private static $log = null;

    public static function initialise(?\Log $log = null)
    {
        self::$enabled = true;

        if (null !== $log) {
            self::$log = $log;
        }

        // Symphony's exception handler is set twice so we need to call
        // restore_error_handler() twice to clear it out.
        restore_exception_handler();
        restore_exception_handler();

        set_exception_handler(array(__CLASS__, 'handler'));
    }

    public static function handler($ex): void
    {
        try {
            if (true !== self::$enabled) {
                return;
            }

            Cli\display_error_and_exit(
                sprintf(
                    "An uncaught exception occurred in %s around line %d:\r\n%s",
                    $ex->getFile(),
                    $ex->getLine(),
                    $ex->getMessage(),
                    ),
                sprintf('[%s]', (new \ReflectionClass($ex))->getName()),
                Colour::BG_RED,
                $ex->getPrevious() instanceof \Exception
                    ? $ex->getPrevious()->getTrace()
                    : $ex->getTrace()
            );
        } catch (\Exception $ex) {
            echo 'Looks like the Exception handler crapped out';
            print_r($ex);
        }

        exit(1);
    }
}
