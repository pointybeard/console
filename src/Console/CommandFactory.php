<?php declare(strict_types=1);

namespace Symphony\Console;

use \ExtensionManager as SymphonyExtensionManager;
use \Extension as SymphonyExtension;

class CommandFactory
{
    const TEMPLATE_NAMESPACE = "\\Symphony\\Console\\Command\\%s\\";

    // Prevents this class from being instanciated
    private function __construct()
    {
    }

    private static function getExtensionStatus($handle)
    {
        $status = SymphonyExtensionManager::fetchStatus(
            SymphonyExtensionManager::about($handle)
        );

        return array_pop($status);
    }

    public static function fetch(string $extension, string $command)
    {
        if (SymphonyExtension::EXTENSION_ENABLED != $status = self::getExtensionStatus($extension)) {
            throw new Exceptions\ExtensionNotEnabledException(
                $extension,
                $status
            );
        }

        CommandAutoloader::init();

        $class = sprintf(self::TEMPLATE_NAMESPACE, $extension) . $command;

        if (!class_exists($class)) {
            throw new Exceptions\CommandNotFoundException($extension, $command);
        }

        $command = new $class;
        if (!($command instanceof AbstractCommand)) {
            throw new Exceptions\CommandInvalidException($extension, $command);
        }

        return $command;
    }
}
