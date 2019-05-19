<?php declare(strict_types=1);

namespace Symphony\Console;

class InputCollection
{
    private $arguments = [];
    private $options = [];

    // Prevents the class from being instanciated
    public function __construct() {}

    public function append(Interfaces\InputTypeInterface $input, bool $replace = false) : self
    {
        $class = new \ReflectionClass($input);
        $this->{"appendInputType" . $class->getShortName()}($input, $replace);
        return $this;
    }

    public function findArgument(string $name, ?int &$index=null) : ?AbstractInputType
    {
        foreach ($this->arguments as $index => $a) {
            if ($a->name() == $name) {
                return $a;
            }
        }

        $index = null;
        return null;
    }

    public function findOption(string $name, ?int &$index=null) : ?AbstractInputType
    {
        $type = strlen($name) == 1 ? 'name' : 'long';

        foreach ($this->options as $index => $o) {
            if ($o->$type() == $name) {
                return $o;
            }
        }

        $index = null;
        return null;
    }

    private function appendInputTypeArgument(Interfaces\InputTypeInterface $argument, bool $replace = false) : void
    {
        if ($this->findArgument($argument->name(), $index) !== null && !$replace) {
            throw new Exceptions\ConsoleException("Argument {$argument->name()} already exists in collection");
        }

        if ($replace == true && !is_null($index)) {
            $this->arguments[$index] = $argument;
        } else {
            $this->arguments[] = $argument;
        }
    }

    private function appendInputTypeOption(Interfaces\InputTypeInterface $option, bool $replace = false) : void
    {
        if ($this->findOption($option->name(), $index) !== null && !$replace) {
            throw new Exceptions\ConsoleException("Option -{$option->name()} already exists in collection");
        }
        if ($replace == true && !is_null($index)) {
            $this->options[$index] = $option;
        } else {
            $this->options[] = $option;
        }
    }

    public function getArgumentsByIndex(int $index) : ?AbstractInputType
    {
        return $this->arguments[$index];
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    public static function merge(self ...$collections) : self
    {
        $arguments = [];
        $options = [];

        foreach ($collections as $c) {
            $arguments = array_merge($arguments, $c->getArguments());
            $options = array_merge($options, $c->getOptions());
        }

        $mergedCollection = new self;

        $it = new \AppendIterator;
        $it->append(new \ArrayIterator($arguments));
        $it->append(new \ArrayIterator($options));

        foreach ($it as $input) {
            try {
                $mergedCollection->append($input, true);
            } catch (Exceptions\ConsoleException $ex) {
                // Already exists, so skip it.
            }
        }

        return $mergedCollection;
    }
}
