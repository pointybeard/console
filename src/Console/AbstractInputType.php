<?php declare(strict_types=1);

namespace Symphony\Console;

abstract class AbstractInputType implements Interfaces\InputTypeInterface
{
    protected static $type;

    protected $name;
    protected $flags;
    protected $description;
    protected $validator;

    protected $value;

    const FLAG_REQUIRED = 0x0001;
    const FLAG_OPTIONAL = 0x0002;
    const FLAG_VALUE_REQUIRED = 0x0004;
    const FLAG_VALUE_OPTIONAL = 0x0008;

    const FLAG_TYPE_STRING = 0x0100;
    const FLAG_TYPE_INT = 0x0200;
    const FLAG_TYPE_INCREMENTING = 0x0400;

    public function __construct($name, int $flags = null, string $description = null, object $validator = null)
    {
        $this->name = $name;
        $this->flags = $flags;
        $this->description = $description;
        $this->validator = $validator;
    }

    public function __call($name, array $args = [])
    {
        return $this->$name;
    }

    public function getType()
    {
        return static::$type;
    }
}
