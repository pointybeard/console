<?php declare(strict_types=1);

namespace Symphony\Console;

use \ExtensionManager as SymphonyExtensionManager;
use \Extension as SymphonyExtension;

final class CommandFactory extends AbstractFactory
{
    protected static $templateNamespace = __NAMESPACE__ . '\\Commands\\%s\\%s';
    protected static $expectedClassType = __NAMESPACE__ . '\\Interfaces\\CommandInterface';

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
        try{
            $command = self::instanciate(
                self::generateTargetClassName(ucfirst($extension), ucfirst($command))
            );
        } catch(\Exception $ex) {
            throw new Exceptions\UnableToLoadCommandException($extension, $command, 0, $ex);
        }

        return $command;
    }
}
