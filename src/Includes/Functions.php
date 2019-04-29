<?php declare(strict_types=1);

/**
 * Checks if bash can be invoked.
 *
 * Credit to Troels Knak-Nielsen
 * (http://www.sitepoint.com/interactive-cli-password-prompt-in-php/) for
 * inspiring this code.
 *
 * @return bool true if bash can be invoked
 */
if (!function_exists("can_invoke_bash")) {
    function can_invoke_bash() : bool
    {
        return (strcmp(trim(shell_exec("/usr/bin/env bash -c 'echo OK'")), 'OK') === 0);
    }
}

/**
 * Checks if script is running as root user
 *
 * @return bool true if user is root
 */
if (!function_exists("is_su")) {
    function is_su() : bool
    {
        $userinfo = posix_getpwuid(posix_geteuid());
        return (bool)($userinfo['uid'] == 0 || $userinfo['name'] == 'root');
    }
}

/**
 * Convienence function for determining if a flag constant is set
 *
 * @return boolean true if the flag is set
 */
if (!function_exists("is_flag_set")) {
    function is_flag_set(int $flags, int $flag) : bool
    {
        // Flags support bitwise operators so it's easy to see
        // if one has been set.
        return ($flags & $flag) == $flag;
    }
}

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

/**
 * Credit to ju1ius for original version of this method
 * https://www.php.net/manual/en/function.wordwrap.php#107570
 * @return string word wrapped string
 */
if (!function_exists("utf8_wordwrap")) {
    function utf8_wordwrap(string $string, int $width=80, string $break=PHP_EOL, bool $cut=false) : string
    {

        // Safety first!
        $width_quoted = preg_quote((string)$width);
        $break_quoted = preg_quote($break);

        // Default pattern and replace. Will not cut words in half.
        $pattern = "@(?=\s)(.{1,{$width_quoted}})(?:\s|\$)@uS";
        $replace = "\$1{$break_quoted}";

        // This will cut in the middle of a word
        if ($cut !== false) {
            $pattern = "@(.{1,{$width_quoted}})(?:\s|\$)|(.{{$width_quoted}})@uS";
            $replace = "\$1\$2{$break_quoted}";
        }

        return trim(preg_replace($pattern, $replace, $string), $break);
    }
}

/**
 * Uses utf8_wordwrap() and then splits by $break to create an array of strings
 * no longer than $width.
 * @see utf8_wordwrap()
 * @return array an array of strings
 */
if (!function_exists("utf8_wordwrap_array")) {
    function utf8_wordwrap_array(string $string, int $width=80, string $break=PHP_EOL, bool $cut=false) : array
    {
        $modified = utf8_wordwrap($string, $width, $break, $cut);
        return preg_split('@'.preg_quote($break).'@', $modified);
    }
}

/**
 * This function will convert $value input to a string but first inspect
 * to see what type of value has been passed in. Allows easy conversion of
 * boolean to true/false literal string value. Also safely converts array,
 * object, resource, and 'unknown type' to string representation.
 * @return string
 */
if (!function_exists("type_sensitive_strval")) {
    function type_sensitive_strval($value) : string
    {
        $result = null;
        $type = gettype($value);

        switch($type) {
            case 'boolean':
                $result = $value === true ? 'true' : 'false';
                break;

            case 'null':
                $result = 'NULL';
                break;

            case 'object':
            case 'array':
            case 'resource':
            case 'unknown type':
                $result = $type;
                break;

            default:
                $result = (string)$value;
                break;
        }

        return $result;
    }
}
