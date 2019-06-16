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

            if (0 == strcasecmp($extension, 'workspace')) {
                $file = sprintf(
                    '%s/commands/%s.php',
                    WORKSPACE,
                    $command
                );
            } else {
                $file = sprintf(
                    '%s/%s/commands/%s.php',
                    EXTENSIONS,
                    $extension,
                    $command
                );
            }

            if (is_readable($file)) {
                require_once $file;
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
