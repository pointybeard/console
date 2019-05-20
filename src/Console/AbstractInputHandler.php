<?php

declare(strict_types=1);

namespace Symphony\Console;

use pointybeard\Helpers\Functions\Flags;

abstract class AbstractInputHandler implements Interfaces\InputHandlerInterface
{
    protected $options = [];
    protected $arguments = [];
    protected $collection = null;

    abstract protected function parse(): bool;

    public function bind(InputCollection $inputCollection, bool $skipValidation = false): bool
    {
        // Do the binding stuff here
        $this->options = [];
        $this->arguments = [];
        $this->collection = $inputCollection;

        $this->parse();

        if (true !== $skipValidation) {
            $this->validate();
        }

        return true;
    }

    private static function checkRequiredAndRequiredValue(AbstractInputType $input, array $context): void
    {
        if (!isset($context[$input->name()])) {
            if (Flags\is_flag_set($input->flags(), AbstractInputType::FLAG_REQUIRED)) {
                throw new Exceptions\RequiredInputMissingException($input);
            }
        } elseif (Flags\is_flag_set($input->flags(), AbstractInputType::FLAG_VALUE_REQUIRED) && (null == $context[$input->name()] || true === $context[$input->name()])) {
            throw new Exceptions\RequiredInputMissingValueException($input);
        }
    }

    public function validate(): void
    {
        // Do basic missing option and value checking here
        foreach ($this->collection->getOptions() as $input) {
            self::checkRequiredAndRequiredValue($input, $this->options);
        }

        // Option validation. Check options first to allow the -h (--help) flag to trigger correctly
        foreach ($this->collection->getoptions() as $o) {
            $result = false;

            if (!array_key_exists($o->name(), $this->options)) {
                $result = $o->default();
            } else {
                if (null === $o->validator()) {
                    $result = $o->default();
                    break;
                } elseif ($o->validator() instanceof \Closure) {
                    $validator = new Validator($o->validator());
                } elseif ($o->validator() instanceof Validator) {
                    $validator = $o->validator();
                } else {
                    throw new Exceptions\ConsoleException("Validator for option {$o->name()}  must be NULL or an instance of either Closure or Console\Validator.");
                }

                $result = $validator->validate($o, $this);
            }

            $this->options[$o->name()] = $result;
        }

        // Argument validation.
        foreach ($this->collection->getArguments() as $a) {
            self::checkRequiredAndRequiredValue($a, $this->arguments);

            if (isset($this->arguments[$a->name()]) && null !== $a->validator()) {
                if ($a->validator() instanceof \Closure) {
                    $validator = new Validator($a->validator());
                } elseif ($a->validator() instanceof Validator) {
                    $validator = $a->validator();
                } else {
                    throw new Exceptions\ConsoleException("Validator for argument {$a->name()} must be NULL or an instance of either Closure or Console\Validator.");
                }

                $validator->validate($a, $this);
            }
        }
    }

    public function getArgument(string $name): ?string
    {
        return $this->arguments[$name] ?? null;
    }

    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getCollection(): ?InputCollection
    {
        return $this->collection;
    }
}
