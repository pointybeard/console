<?php declare(strict_types=1);

namespace Symphony\Console;

final class CommandAutoloader
{
    private static $initialised = false;

    public static function init() : void
    {

        // Only allow this to be called once. It's okay to silently return.
        if (self::$initialised == true) {
            return;
        }

        // This custom autoloader checks the WORKSPACE/bin/ directory for a
        // matching command. We *could* just use the PSR4 autoloader, however,
        // this will allow autoloading from a dynamically set WORKSPACE folder
        spl_autoload_register(function ($className) {
            if (!preg_match(
                '/^Symphony\\\\Console\\\\Command\\\\Workspace\\\\(.+)$/i',
                $className,
                $matches
            )) {
                return;
            }

            $file = WORKSPACE . "/bin/" . str_replace('\\', '/', $matches[1]);

            if (is_readable($file)) {
                require $file;
            }
        });

        // Autoload commands in an extensions /bin folder
        spl_autoload_register(function ($className) {
            if (!preg_match_all(
                '/^Symphony\\\\Console\\\\Command\\\\([^\\\\]+)\\\\(.+)$/i',
                $className,
                $matches
            )) {
                return;
            }

            $file = sprintf(
                "%s/%s/bin/%s",
                EXTENSIONS,
                $matches[1][0],
                $matches[2][0]
            );

            if (is_readable($file)) {
                require $file;
            }
        });

        // Autoloader for Extension driver classes
        spl_autoload_register(function ($className) {
            if (!preg_match_all('/^Extension_(.*)$/i', $className, $matches)) {
                return;
            }

            // Check if Extension is enabled
            // @TODO

            $file = sprintf(
                "%s/%s/extension.driver.php",
                EXTENSIONS,
                strtolower($matches[1][0])
            );

            if (is_readable($file)) {
                require $file;
            }
        });

        self::$initialised = true;
    }

    public static function fetch() : array
    {
        $commands = [];

        $path = realpath(WORKSPACE . '/bin');

        if ($path !== false && is_dir($path) && is_readable($path)) {
            foreach (new \DirectoryIterator($path) as $f) {
                if ($f->isDot()) {
                    continue;
                }
                $commands['workspace'][] = $f->getFilename();
            }
        }

        foreach (new \DirectoryIterator(EXTENSIONS) as $d) {
            if ($d->isDot() || !$d->isDir() || !is_dir($d->getPathname() . '/bin')) {
                continue;
            }

            foreach (new \DirectoryIterator($d->getPathname() . '/bin') as $f) {
                if (
                    $f->isDot() ||
                    !preg_match_all(
                        '/extensions\/([^\/]+)\/bin\/([^\.\/]+)$/i',
                        $f->getPathname(),
                        $matches,
                        PREG_SET_ORDER
                    )
                ) {
                    continue;
                }

                list(, $extension, $command) = $matches[0];

                // Skip over the core 'symphony' command
                if ($extension == 'console' && $command == 'symphony') {
                    continue;
                }

                $commands[$extension][] = $command;
            }
        }

        return $commands;
    }
}
