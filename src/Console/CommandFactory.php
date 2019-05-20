<?php

declare(strict_types=1);

namespace Symphony\Console;

use ExtensionManager as SymphonyExtensionManager;
use Extension as SymphonyExtension;
use pointybeard\Helpers\Foundation\Factory;

final class CommandFactory extends Factory\AbstractFactory
{
    public static function getTemplateNamespace(): string
    {
        return __NAMESPACE__.'\\Commands\\%s\\%s';
    }

    public static function getExpectedClassType(): ?string
    {
        return __NAMESPACE__.'\\Interfaces\\CommandInterface';
    }

    private static function getExtensionStatus($handle)
    {
        $status = SymphonyExtensionManager::fetchStatus(
            SymphonyExtensionManager::about($handle)
        );

        return array_pop($status);
    }

    public static function build(string $extension, string $command)
    {
        if (SymphonyExtension::EXTENSION_ENABLED != $status = self::getExtensionStatus($extension)) {
            throw new Exceptions\ExtensionNotEnabledException(
                $extension,
                $status
            );
        }

        CommandAutoloader::init();

        // Note it is important to capitalise the first character of both
        // $extension and $command.
        try {
            $command = self::instanciate(
                self::generateTargetClassName(ucfirst($extension), ucfirst($command))
            );
        } catch (\Exception $ex) {
            throw new Exceptions\UnableToLoadCommandException($extension, $command, 0, $ex);
        }

        return $command;
    }
}
