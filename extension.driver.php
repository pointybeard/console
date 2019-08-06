<?php

declare(strict_types=1);

if (!file_exists(__DIR__.'/vendor/autoload.php')) {
    throw new Exception(sprintf(
        'Could not find composer autoload file %s. Did you run `composer update` in %s?',
        __DIR__.'/vendor/autoload.php',
        __DIR__
    ));
}

require_once __DIR__.'/vendor/autoload.php';

// Check if the class already exists before declaring it again.
if (!class_exists('\\Extension_Console')) {
    class Extension_Console extends Extension
    {
        public static function init()
        {
        }
    }
}
