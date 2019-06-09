<?php

declare(strict_types=1);

namespace Symphony\Console;

use pointybeard\Helpers\Foundation\Factory;

final class CommandFactory extends Factory\AbstractFactory
{
    public function getTemplateNamespace(): string
    {
        return __NAMESPACE__.'\\Commands\\%s\\%s';
    }

    public function getExpectedClassType(): ?string
    {
        return __NAMESPACE__.'\\Interfaces\\CommandInterface';
    }

    public static function build(string $extension, ...$arguments): object
    {
        $factory = new self();

        // We only need to care about the first item in $arguments
        [$command] = $arguments;

        if ('workspace' != $extension && \Extension::EXTENSION_ENABLED != $status = CommandAutoloader::getExtensionStatus($extension)) {
            throw new Exceptions\ExtensionNotEnabledException(
                $extension,
                $status
            );
        }

        CommandAutoloader::init();

        // Note it is important to capitalise the first character of both
        // $extension and $command.
        try {
            $command = $factory->instanciate(
                $factory->generateTargetClassName(ucfirst($extension), ucfirst($command))
            );
        } catch (\Exception $ex) {
            throw new Exceptions\UnableToLoadCommandException($extension, $command, 0, $ex);
        }

        return $command;
    }
}
