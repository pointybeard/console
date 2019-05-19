<?php declare(strict_types=1);

namespace Symphony\Console\Functions;

use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Functions\Arrays;

if (!function_exists(__NAMESPACE__ . "\is_path_absolute")) {
function is_path_absolute($path) {
    return strstr($path, "..") == false;
}
}

// Thanks to Gordon for the original function implementation that this is based
// on (https://stackoverflow.com/a/2638272)
if (!function_exists(__NAMESPACE__ . "\get_relative_path")) {
function get_relative_path(string $from, string $to, bool $strict=true) : string
{

    if($strict == true) {
        if(!is_path_absolute($from) && null == ($from = realpath($from))) {
            throw new \Exception("path {$from} is relative and does not exist! Make sure path exists (or set \$strict to false)");
        }

        if(!is_path_absolute($to) && null == ($to = realpath($to))) {
            throw new \Exception("path {$to} is relative and does not exist! Make sure path exists (or set \$strict to false)");
        }
    }

    $bitsFrom = explode(DIRECTORY_SEPARATOR, $from);
    $bitsTo = explode(DIRECTORY_SEPARATOR, $to);

    $relativePathBits = $bitsTo;

    foreach($bitsFrom as $depth => $dir) {
        if(!isset($bitsTo[$depth])) {
            // There are fewer directories in the $to path than the $from path
            // which means we're traversing up but not changing directory
            // or file name. See how many bits are left in $from path and
            // add that many '..' values
            $remaining = count($bitsFrom) - $depth;
            $relativePathBits = array_pad([], $remaining, '..');
            break;

        } elseif(strcmp($dir, $bitsTo[$depth]) == 0) {
            // The current $dir is the same as the item in the $to path
            // at $depth. Shift it out of the $relativePathBits and keep going
            array_shift($relativePathBits);

        } else {
            // $dir and $bitsTo[$depth] don't match, so we're as far as we can go.
            // See how many bits are left in the from path, then add that
            // many '..' items to the start of the $relativePathBits array
            $remaining = count($bitsFrom) - $depth;

            if($remaining <= 1) {
                // There is exactly one item left in the $from path. This
                // means we're only a single directory away.
                array_unshift($relativePathBits, '.');

            } else {
                array_unshift($relativePathBits, ...array_pad(
                    [], $remaining, '..'
                ));
                break;
            }
        }
    }

    // Join all the relative path bits to form the final relative path
    return implode(DIRECTORY_SEPARATOR, $relativePathBits);
}
}

/**
 * Convienence function for displaying a red console error without using
 * the Console/Message class
 */
if (!function_exists(__NAMESPACE__ . "\console_fatal_error")) {
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
