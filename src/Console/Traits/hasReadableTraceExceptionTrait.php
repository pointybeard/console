<?php declare(strict_types=1);

namespace Symphony\Console\Traits;

trait hasReadableTraceExceptionTrait
{
    public function getReadableTrace() : ?string
    {
        $trace = null;
        if (count($this->getTrace()) > 0) {
            foreach ($this->getTrace() as $line) {
                $trace .= sprintf(
                    '[%s:%d] %s%s%s();' . PHP_EOL,
                    (isset($line['file']) ? $line['file'] : null),
                    (isset($line['line']) ? $line['line'] : null),
                    (isset($line['class']) ? $line['class'] : null),
                    (isset($line['type']) ? $line['type'] : null),
                    $line['function']
                );
            }
        }
        return $trace;
    }
}
