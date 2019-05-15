<?php declare(strict_types=1);

use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Functions\Arrays;

/**
 * Convienence function for displaying a red console error without using
 * the Console/Message class
 */
if (!function_exists("console_fatal_error")) {
    function console_fatal_error($message, $heading = "SYMPHONY CONSOLE FATAL ERROR") : void
    {
        $wrap_with_red = function (string $string, int $pad = 5, bool $bold = false) : string {
            $string = str_pad('', $pad, ' ') . $string . str_pad('', $pad, ' ');
            return "\e[" . ($bold ? 1 : 0) . ";37m\e[41m{$string}\033[0m";
        };

        $emptyLine = '    ' . $wrap_with_red(str_pad('', 40, ' ', \STR_PAD_RIGHT), 5, true);
        $heading = str_pad(trim($heading), 40, ' ', \STR_PAD_RIGHT);

        $message = Strings\utf8_wordwrap_array($message, strlen($heading) - 10);

        // Remove surrounding whitespace
        $message = array_map("trim", $message);

        // Remove empty elements from the array
        $message = Arrays\array_remove_empty($message);

        // Reset array indicies
        $message = array_values($message);

        // Check for a backtrace and get it's index if there is one
        $traceArrayIndex = array_search("Trace", $message);
        if ($traceArrayIndex !== false) {
            // Purely cosmetic; add a new line before the trace starts
            $message[$traceArrayIndex] = PHP_EOL . $message[$traceArrayIndex];
        }

        // Wrap everything, except a trace, in red
        for ($ii = 0; $ii < count($message); $ii++) {
            if ($traceArrayIndex !== false && $ii == $traceArrayIndex) {
                break;
            }
            $message[$ii] = '    ' . $wrap_with_red(str_pad(
                $message[$ii],
                strlen($heading),
                ' ',
                \STR_PAD_RIGHT
            ));
        }

        // Add an empty red line before the trace (or at the end if there
        // is no trace)
        Arrays\array_insert_at_index(
            $message,
            $traceArrayIndex !== false
                ? $traceArrayIndex
                : count($message),
            $emptyLine
        );

        // Print the error message, starting with an empty red line
        printf(
            "\r\n%s\r\n    %s\r\n%s\r\n",
            $emptyLine,
            $wrap_with_red($heading, 5, true),
            implode($message, PHP_EOL)
        );

        exit(1);
    }
}
