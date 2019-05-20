<?php

declare(strict_types=1);

namespace Symphony\Console;

use Extension as SymphonyExtension;

final class CommandAutoloader
{
    private static $initialised = false;

    public static function init(): void
    {
        // Only allow this to be called once. It's okay to silently return.
        if (true == self::$initialised) {
            return;
        }

        // This custom autoloader checks the WORKSPACE/commands/ directory for
        // matching a command.
        spl_autoload_register(function ($class) {
            if (!preg_match(
                '/^Symphony\\\\Console\\\\Commands\\\\Workspace\\\\(.+)$/i',
                $class,
                $matches
            )) {
                return;
            }

            $file = WORKSPACE.'/commands/'.str_replace('\\', '/', $matches[1]);

            if (is_readable($file)) {
                require $file;
            }
        });

        // Autoload commands in an extensions /commands folder
        spl_autoload_register(function ($class) {
            if (!preg_match_all(
                '/^Symphony\\\\Console\\\\Commands\\\\([^\\\\]+)\\\\(.+)$/i',
                $class,
                $matches
            )) {
                return;
            }

            $file = sprintf(
                '%s/%s/commands/%s.php',
                EXTENSIONS,
                $matches[1][0],
                $matches[2][0]
            );

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
            if (SymphonyExtension::EXTENSION_ENABLED != $status = self::getExtensionStatus($extension)) {
                return;
            }

            $path = sprintf(
                '%s/%s',
                EXTENSIONS,
                $extension
            );

            if (is_readable("{$path}/extension.driver.php")) {
                require_once "{$path}/extension.driver.php";
            }

            if (is_readable("{$path}/vendor/autoload.php")) {
                require_once "{$path}/vendor/autoload.php";
            }
        });

        self::$initialised = true;
    }

    public static function fetch(): array
    {
        $commands = [];

        $path = realpath(WORKSPACE.'/commands');

        if (false !== $path && is_dir($path) && is_readable($path)) {
            foreach (new \DirectoryIterator($path) as $f) {
                if ($f->isDot()) {
                    continue;
                }
                $commands['workspace'][] = $f->getFilename();
            }
        }

        foreach (new \DirectoryIterator(EXTENSIONS) as $d) {
            if ($d->isDot() || !$d->isDir() || !is_dir($d->getPathname().'/commands')) {
                continue;
            }

            foreach (new \DirectoryIterator($d->getPathname().'/commands') as $f) {
                if (
                    $f->isDot() ||
                    !preg_match_all(
                        '/extensions\/([^\/]+)\/commands\/([^\.\/]+)\.php$/i',
                        $f->getPathname(),
                        $matches,
                        PREG_SET_ORDER
                    )
                ) {
                    continue;
                }

                list(, $extension, $command) = $matches[0];

                // Skip over the core 'symphony' command
                if ('console' == $extension && 'symphony' == $command) {
                    continue;
                }

                $commands[$extension][] = $command;
            }
        }

        return $commands;
    }
}
