<?php declare(strict_types=1);

namespace Symphony\Console;

class Validator implements Interfaces\InputValidatorInterface
{
    private $func;
    public function __construct(\Closure $func)
    {
        // Check the closure used for validation meets requirements
        $params = (new \ReflectionFunction($func))->getParameters();

        // Must have exactly 2 params
        if (count($params) <> 2) {
            throw new Exceptions\ConsoleException("Closure passed to Validator::__construct() is invalid: Must have exactly 2 parameters.");
        }

        // First must be 'input' and be of type Symphony\Console\AbstractInputType
        if ($params[0]->getName() != 'input' || (string)$params[0]->getType() != 'Symphony\Console\AbstractInputType') {
            throw new Exceptions\ConsoleException("Closure passed to Validator::__construct() is invalid: First parameter must match Symphony\Console\AbstractInputType \$input. Provided with " . (string)$params[0]->getType() . " \${$params[0]->getName()}");
        }

        // Second must be 'context' and be of type Symphony\Console\AbstractInputHandler
        if ($params[1]->getName() != 'context' || (string)$params[1]->getType() != 'Symphony\Console\AbstractInputHandler') {
            throw new Exceptions\ConsoleException("Closure passed to Validator::__construct() is invalid: Second parameter must match Symphony\Console\AbstractInputHandler \$context. Provided with " . (string)$params[1]->getType() . " \${$params[1]->getName()}");
        }

        $this->func = $func;
    }

    public function validate(AbstractInputType $input, AbstractInputHandler $context)
    {
        return ($this->func)($input, $context);
    }
}
