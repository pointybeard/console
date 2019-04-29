<?php declare(strict_types=1);

namespace Symphony\Console;

class Prompt
{
    const FLAG_SILENT = 0x001;

    /**
     * This function waits for input from $target (default is STDIN). Support
     * silent input by setting $silent=true However this requires bash. If
     * bash is not available, then it will trigger a E_USER_NOTICE error and
     * fallback to the "non-silent" method.
     *
     * Credit to Troels Knak-Nielsen
     * (http://www.sitepoint.com/interactive-cli-password-prompt-in-php/) for
     * inspiring most of this code.
     *
     * @param  string $prompt
     *                        This is displayed before reading any input.
     * @param  bool   $silent
     *                        Turns off echoing of input to CLI. Useful
     *                        for passwords. Only works if bash is avilable.
     * @return string
     */
    public static function display($prompt, $flags = null, $default = null, \Closure $validator = null, $character = ":", $target=STDIN) : string
    {
        $silent = is_flag_set($flags, self::FLAG_SILENT);

        if ($silent == true && !can_invoke_bash()) {
            trigger_error("bash cannot be invoked from PHP so 'silent' flag cannot be used.", E_USER_NOTICE);
            $silent = false;
        }

        if (!($prompt instanceof Message)) {
            $prompt = new Message($prompt);
        }

        $prompt->message(sprintf(
            "%s%s%s ",
            $prompt->message,
            (!is_null($default) ? " [{$default}]" : null),
            $character
        ));

        do {
            $prompt
                ->flags(null)
                ->display()
            ;

            if ($silent) {
                if ($target != STDIN) {
                    throw new Exceptions\ConsoleException(
                        "Cannot use silent prompt unless target is STDIN"
                    );
                }

                $input = shell_exec("/usr/bin/env bash -c 'read -s in && echo \$in'");
                echo PHP_EOL;
            } else {
                $input = fgets($target, 256);
            }

            $input = trim($input);

            if (strlen(trim($input)) == 0 && !is_null($default)) {
                $input = $default;
            }
        } while ($validator instanceof \Closure && !$validator($input));

        return $input;
    }
}
