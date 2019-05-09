<?php declare(strict_types=1);

/**
 * Convienence function for displaying a red console error without using
 * the Console/Message class
 */
if (!function_exists("console_fatal_error")) {
    function console_fatal_error($message, $heading = "SYMPHONY CONSOLE FATAL ERROR") : void
    {

        $wrap_with_red = function(string $string, int $pad = 5, bool $bold = false) : string {
            $string = str_pad('', $pad, ' ') . $string . str_pad('', $pad, ' ');
            return "\e[" . ($bold ? 1 : 0) . ";37m\e[41m{$string}\033[0m";
        };

        $heading = str_pad($heading, 40, ' ', \STR_PAD_RIGHT);

        $message = utf8_wordwrap_array($message, strlen($heading) - 10);
        for($ii = 0; $ii < count($message); $ii++) {
            $message[$ii] = "    " . $wrap_with_red(str_pad($message[$ii], strlen($heading), ' ', \STR_PAD_RIGHT));
        }

        printf(PHP_EOL . '    %s%3$s%s%3$s' .  PHP_EOL, $wrap_with_red($heading, 5, true), implode($message, PHP_EOL), PHP_EOL);
        exit(1);
    }
}
