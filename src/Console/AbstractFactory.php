<?php declare(strict_types=1);

namespace Symphony\Console;

abstract class AbstractFactory
{
    protected static $templateNamespace = null;
    protected static $expectedClassType = null;

    // Prevents the class from being instanciated
    private function __construct() {}

    protected static function generateTargetClassName(string ...$args) : string {
        return vsprintf(static::$templateNamespace, $args);
    }

    protected static function isExpectedClassType(object $class) {
        return $class instanceof static::$expectedClassType;
    }

    protected static function instanciate(string $class) : object {
        if (!class_exists($class)) {
            throw new Exceptions\ConsoleWithTraceException(sprintf(
                "Class %s does not exist. Is it provided by an autoloader?", $class
            ));
        }

        $object = new $class;

        if (!static::isExpectedClassType($object)) {
            throw new Exceptions\ConsoleWithTraceException(sprintf(
                "Class %s is not of expected type %s", $class, static::$expectedClassType
            ));
        }

        return $object;
    }
}
