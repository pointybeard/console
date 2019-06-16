<?php

declare(strict_types=1);

namespace pointybeard\Symphony\Extensions\Console;

final class CommandIterator extends \RegexIterator
{
    private $shouldInstanciate;

    public function __construct()
    {
        $iterator = new \AppendIterator();

        foreach (new \DirectoryIterator(EXTENSIONS) as $d) {
            if ($d->isDot() || !$d->isDir() || !is_dir($d->getPathname().'/commands')) {
                continue;
            }
            $iterator->append(new \DirectoryIterator($d->getPathname().'/commands'));
        }

        if (false !== $workspaceCommands = realpath(WORKSPACE.'/commands')) {
            $iterator->append(new \DirectoryIterator($workspaceCommands));
        }

        $commands = new \ArrayIterator();
        foreach ($iterator as $command) {
            $commands->append($command->getPathname());
        }

        parent::__construct(
            $commands,
            '@/([^/]+)/commands/([^\.]+)\.php$@i',
            self::ALL_MATCHES,
            0,
            PREG_SET_ORDER
        );
    }

    public function accept(): bool
    {
        if (false === parent::accept()) {
            return false;
        }

        [$extension, $command] = $this->current();

        // Make sure the extension has been enabled
        if ('workspace' != $extension && \Extension::EXTENSION_ENABLED != $status = CommandAutoloader::getExtensionStatus($extension)) {
            return false;
        }

        // Skip over the core 'symphony' command
        if ('console' == $extension && 'symphony' == $command) {
            return false;
        }

        return true;
    }

    public function current()
    {
        [, $extension, $command] = array_pop(parent::current());

        return [$extension, $command];
    }
}
