<?php

declare(strict_types=1);

namespace Symphony\Console;

abstract class AbstractInputType implements Interfaces\InputTypeInterface
{
    protected static $type;

    protected $name;
    protected $flags;
    protected $description;
    protected $validator;

    protected $value;

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

    public function getType(): string
    {
        return strtolower((new \ReflectionClass(static::class))->getShortName());
    }
}
