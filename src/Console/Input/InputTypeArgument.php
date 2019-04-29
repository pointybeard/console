<?php declare(strict_types=1);

namespace Symphony\Console\Input;

use Symphony\Console as Console;

class InputTypeArgument extends Console\AbstractInputType
{
    protected static $type = "argument";

    public function __toString()
    {
        $name = strtoupper($this->name());

        $first = str_pad(sprintf("%s    ", $name), 20, ' ');

        $second = utf8_wordwrap_array($this->description(), 40);
        for ($ii = 1; $ii < count($second); $ii++) {
            $second[$ii] = str_pad('', 22, ' ', \STR_PAD_LEFT) . $second[$ii];
        }

        return $first . implode($second, PHP_EOL);
    }
}
