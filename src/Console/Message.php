<?php declare(strict_types=1);

namespace Symphony\Console;

class Message
{
    private $message = null;
    private $background = null;
    private $foreground = null;
    private $dateFormat = null;
    private $flags = null;

    const FLAG_NONE = null;
    const FLAG_PREPEND_DATE = 0x001;
    const FLAG_APPEND_NEWLINE = 0x002;

    const DEFAULT_DATE_FORMAT = "H:i:s > ";
    const DEFAULT_FLAGS = self::FLAG_PREPEND_DATE | self::FLAG_APPEND_NEWLINE;

    // Credit to JR from http://www.if-not-true-then-false.com/
    // for the code reponsible for colourising the messages
    const FG_COLOUR_DEFAULT = '0;39';
    const FG_COLOUR_BLACK = '0;30';
    const FG_COLOUR_RED = '0;31';
    const FG_COLOUR_GREEN = '0;32';
    const FG_COLOUR_BROWN = '0;33';
    const FG_COLOUR_BLUE = '0;34';
    const FG_COLOUR_PURPLE = '0;35';
    const FG_COLOUR_CYAN = '0;36';
    const FG_COLOUR_WHITE = '1;37';
    const FG_COLOUR_DARK_GRAY = '1;30';
    const FG_COLOUR_LIGHT_RED = '1;31';
    const FG_COLOUR_LIGHT_GREEN = '1;32';
    const FG_COLOUR_YELLOW = '1;33';
    const FG_COLOUR_LIGHT_BLUE = '1;34';
    const FG_COLOUR_LIGHT_PURPLE = '1;35';
    const FG_COLOUR_LIGHT_CYAN = '1;36';
    const FG_COLOUR_LIGHT_GRAY = '0;37';

    const BG_COLOUR_BLACK = '40';
    const BG_COLOUR_RED = '41';
    const BG_COLOUR_GREEN = '42';
    const BG_COLOUR_YELLOW = '43';
    const BG_COLOUR_BLUE = '44';
    const BG_COLOUR_MAGENTA = '45';
    const BG_COLOUR_CYAN = '46';
    const BG_COLOUR_DEFAULT = '49';
    const BG_COLOUR_WHITE = '107';
    const BG_COLOUR_LIGHT_GRAY = '47';
    const BG_COLOUR_LIGHT_RED = '101';
    const BG_COLOUR_LIGHT_GREEN = '102';
    const BG_COLOUR_LIGHT_YELLOW = '103';
    const BG_COLOUR_LIGHT_BLUE = '104';
    const BG_COLOUR_LIGHT_MAGENTA = '105';
    const BG_COLOUR_LIGHT_CYAN = '106';
    const BG_COLOUR_DARK_GRAY = '100';

    private static $foregroundColours = [
        'default'       => self::FG_COLOUR_DEFAULT,
        'black'         => self::FG_COLOUR_BLACK,
        'red'           => self::FG_COLOUR_RED,
        'green'         => self::FG_COLOUR_GREEN,
        'brown'         => self::FG_COLOUR_BROWN,
        'blue'          => self::FG_COLOUR_BLUE,
        'purple'        => self::FG_COLOUR_PURPLE,
        'cyan'          => self::FG_COLOUR_CYAN,
        'white'         => self::FG_COLOUR_WHITE,
        'dark gray'     => self::FG_COLOUR_DARK_GRAY,
        'light red'     => self::FG_COLOUR_LIGHT_RED,
        'light green'   => self::FG_COLOUR_LIGHT_GREEN,
        'yellow'        => self::FG_COLOUR_YELLOW,
        'light blue'    => self::FG_COLOUR_LIGHT_BLUE,
        'light purple'  => self::FG_COLOUR_LIGHT_PURPLE,
        'light cyan'    => self::FG_COLOUR_LIGHT_CYAN,
        'light gray'    => self::FG_COLOUR_LIGHT_GRAY,
    ];

    private static $backgroundColours = [
        'black'         => self::BG_COLOUR_BLACK,
        'red'           => self::BG_COLOUR_RED,
        'green'         => self::BG_COLOUR_GREEN,
        'yellow'        => self::BG_COLOUR_YELLOW,
        'blue'          => self::BG_COLOUR_BLUE,
        'magenta'       => self::BG_COLOUR_MAGENTA,
        'cyan'          => self::BG_COLOUR_CYAN,
        'default'       => self::BG_COLOUR_DEFAULT,
        'white'         => self::BG_COLOUR_WHITE,
        'light gray'    => self::BG_COLOUR_LIGHT_GRAY,
        'light red'     => self::BG_COLOUR_LIGHT_RED,
        'light green'   => self::BG_COLOUR_LIGHT_GREEN,
        'light yellow'  => self::BG_COLOUR_LIGHT_YELLOW,
        'light blue'    => self::BG_COLOUR_LIGHT_BLUE,
        'light magenta' => self::BG_COLOUR_LIGHT_MAGENTA,
        'light cyan'    => self::BG_COLOUR_LIGHT_CYAN,
        'dark gray'     => self::BG_COLOUR_DARK_GRAY,
    ];

    public function __get($name)
    {
        return $this->$name;
    }

    public function __construct(string $message = null, ?string $foregroundColour = self::FG_COLOUR_DEFAULT, ?string $backgroundColour = self::BG_COLOUR_DEFAULT, ?int $flags = self::DEFAULT_FLAGS, string $dateFormat=self::DEFAULT_DATE_FORMAT)
    {
        if (!is_null($message)) {
            $this->message($message);
        }

        $this
            ->foreground($foregroundColour)
            ->background($backgroundColour)
            ->flags($flags)
            ->dateFormat($dateFormat)
        ;
    }

    public function message(string $message) : self
    {
        $this->message = $message;
        return $this;
    }

    public function foreground(?string $colour) : self
    {
        if (!is_null($colour) && !in_array($colour, self::$foregroundColours)) {
            throw new Exceptions\NoSuchColourException(
                $colour,
                array_keys(self::$foregroundColours)
            );
        }
        $this->foreground = $colour;
        return $this;
    }

    public function background(?string $colour) : self
    {
        if (!is_null($colour) && !in_array($colour, self::$backgroundColours)) {
            throw new Exceptions\NoSuchColourException(
                $colour,
                array_keys(self::$backgroundColours)
            );
        }
        $this->background = $colour;
        return $this;
    }

    public function dateFormat(string $format) : self
    {
        $this->dateFormat = $format;
        return $this;
    }

    public function flags(?int $flags) : self
    {
        $this->flags = $flags;
        return $this;
    }

    public function display($target=STDOUT) : int
    {
        return fwrite($target, (string)$this);
    }

    public function __toString()
    {
        $message = null;

        if (!is_null($this->foreground)) {
            $message .= "\e[" . $this->foreground . "m";
        }

        if (!is_null($this->background)) {
            $message .= "\e[" . $this->background . "m";
        }

        // Add string and end coloring
        $message .=  $this->message . "\033[0m";

        return sprintf(
            '%s%s%s',
            (
                is_flag_set($this->flags, self::FLAG_PREPEND_DATE)
                    ? self::now($this->dateFormat)
                    : null
            ),
            (string)$message,
            (
                is_flag_set($this->flags, self::FLAG_APPEND_NEWLINE)
                    ? PHP_EOL
                    : null
            )
        );
    }

    private static function now(string $format = self::DEFAULT_DATE_FORMAT) : string
    {
        return (new \DateTime)->format($format);
    }
}
