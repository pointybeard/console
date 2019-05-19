<?php declare(strict_types=1);

namespace Symphony\Console\Traits;
use Symphony\Console\Functions;

trait hasReadableTraceExceptionTrait
{
    public function getReadableTrace() : ?string
    {
        // Doesn't make sense for this trait to be included in
        // non-exception based classes, but we should check anyway.
        if(!($this instanceof \Exception)) {
            throw new \Exception("Can only call getReadableTrace() on an Exception");

        // Nothing in the trace
        } elseif (count($this->getTrace()) <= 0) {
            return null;
        }

        $traceLineFormat = '[%s:%d] %s%s%s();' . PHP_EOL;

        $trace = null;

        $baseLine = [
            'relative' => null,
            'line' => null,
            'class' => null,
            'type' => null,
            'function' => null,
            'file' => null,
            'args' => []
        ];

        foreach ($this->getTrace() as $line) {

            if($line['file'] !== null) {
                try{
                    $line['relative'] = Functions\get_relative_path(getcwd(), $line['file']);

                // Something when wrong. Just use the full file path instead
                } catch (\Exception $ex) {
                    $line['relative'] = $line['file'];
                }
            }

            // This will keep values from $line but order them according to
            // $baseLine's array keys, otherwise the result from vsprintf will
            // be wonky
            $line = array_merge($baseLine, $line);

            $trace .= vsprintf(
                $traceLineFormat,
                array_slice($line, 0, 5) // We don't care about 'file' or 'args'
            );
        }

        return $trace;
    }
}
