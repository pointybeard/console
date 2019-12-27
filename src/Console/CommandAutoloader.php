<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console;

final class CommandAutoloader
{
    private static $initialised = false;

    public static function getExtensionStatus($handle)
    {
        $status = \ExtensionManager::fetchStatus(
            \ExtensionManager::about($handle)
        );

        return array_pop($status);
    }

    public static function init(): void
    {
        // Only allow this to be called once. It's okay to silently return.
        if (true == self::$initialised) {
            return;
        }

        // Autoload commands in an extensions /commands or workspace/commands
        // folder
        spl_autoload_register(function ($class) {
            if (!preg_match_all(
                sprintf(
                    '@%s\\\\Commands\\\\([^\\\\]+)\\\\(.+)$@i',
                    preg_quote(__NAMESPACE__)
                ),
                $class,
                $matches
            )) {
                return;
            }

            $extension = $matches[1][0];
            $command = $matches[2][0];

            $filepaths = [];

            $generateFilePathCombinations = function($subjects, string $delim=DIRECTORY_SEPARATOR, string $pre=null, string $post=null, array $operations = ["strtolower", "ucfirst", "lcfirst"]): array {
                if(!is_array($subjects)) {
                    $subjects = [$subjects];
                }

                $sets = array_fill_keys($subjects, []);

                foreach($subjects as $s) {
                    $sets[$s][] = $s;
                    foreach($operations as $func) {
                        $sets[$s][] = call_user_func($func, $s);
                    }
                    $sets[$s] = array_unique($sets[$s]);
                }

                $combinations = [[]];
                foreach($sets as $index => $values) {
                    $tmp = [];
                    foreach($combinations as $r) {
                        foreach($values as $v) {
                            $r[$index] = $v;
                            $tmp[] = $r;
                        }
                    }
                    $combinations = $tmp;
                }

                $result = [];
                foreach($combinations as $items) {
                    $result[] = $pre . implode($items, $delim) . $post;
                }

                return array_unique($result);

            };

            if (0 == strcasecmp($extension, 'workspace')) {
                $filepaths = $generateFilePathCombinations(
                    ['commands', $command],
                    DIRECTORY_SEPARATOR,
                    WORKSPACE . DIRECTORY_SEPARATOR,
                    ".php"
                );
            } else {
                $filepaths = $generateFilePathCombinations(
                    [$extension, 'commands', $command],
                    DIRECTORY_SEPARATOR,
                    EXTENSIONS . DIRECTORY_SEPARATOR,
                    ".php"
                );
            }

            foreach ($filepaths as $file) {
                if (is_readable($file)) {
                    require_once $file;
                    break;
                }
            }
        });

        // Autoloader for Extension driver classes
        spl_autoload_register(function ($class) {
            if (!preg_match('/^Extension_(.*)$/i', $class, $matches)) {
                return;
            }

            $extension = strtolower($matches[1]);

            // Check if Extension is enabled
            if (\Extension::EXTENSION_ENABLED != $status = self::getExtensionStatus($extension)) {
                return;
            }

            $path = sprintf(
                '%s/%s',
                EXTENSIONS,
                $extension
            );

            if (is_readable($path.'/extension.driver.php')) {
                require_once $path.'/extension.driver.php';
            }

            if (is_readable($path.'/vendor/autoload.php')) {
                require_once $path.'/vendor/autoload.php';
            }
        });

        self::$initialised = true;
    }
}
