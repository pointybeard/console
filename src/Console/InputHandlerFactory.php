<?php

declare(strict_types=1);

namespace Symphony\Console;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Foundation\Factory;

final class InputHandlerFactory extends Factory\AbstractFactory
{
    const FLAG_SKIP_VALIDATION = 0x0001;

    public static function getTemplateNamespace(): string
    {
        return __NAMESPACE__.'\\Input\\Handlers\\%s';
    }

    public static function getExpectedClassType(): ?string
    {
        return __NAMESPACE__.'\\Interfaces\\InputHandlerInterface';
    }

    public static function build(string $name, InputCollection $collection = null, int $flags = null): Interfaces\InputHandlerInterface
    {
        try {
            $handler = self::instanciate(
                self::generateTargetClassName($name)
            );
        } catch (\Exception $ex) {
            throw new Exceptions\UnableToLoadInputHandlerException($name, 0, $ex);
        }

        if ($collection instanceof InputCollection) {
            $handler->bind(
                $collection,
                Flags\is_flag_set($flags, self::FLAG_SKIP_VALIDATION)
            );
        }

        return $handler;
    }
}
