<?php declare(strict_types=1);

namespace Symphony\Console\Input\Types;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Functions\Strings;
use Symphony\Console as Console;

class Option extends Console\AbstractInputType
{
    protected $long;
    protected $default;

    public function __construct(string $name, string $long = null, int $flags = null, string $description = null, object $validator = null, $default = false)
    {
        $this->default = $default;
        $this->long = $long;
        parent::__construct($name, $flags, $description, $validator);
    }

    public function __toString()
    {
        $long = $this->long() !== null ? ', --' . $this->long() : null;
        if ($long != null) {
            if (Flags\is_flag_set($this->flags(), self::FLAG_VALUE_REQUIRED)) {
                $long .= "=VALUE";
            } elseif (Flags\is_flag_set($this->flags(), self::FLAG_VALUE_OPTIONAL)) {
                $long .= "[=VALUE]";
            }
        }
        $first = str_pad(sprintf("-%s%s    ", $this->name(), $long), 36, ' ');

        $second = Strings\utf8_wordwrap_array($this->description(), 40);
        for ($ii = 1; $ii < count($second); $ii++) {
            $second[$ii] = str_pad('', 38, ' ', \STR_PAD_LEFT) . $second[$ii];
        }

        return $first . implode($second, PHP_EOL);
    }
}
