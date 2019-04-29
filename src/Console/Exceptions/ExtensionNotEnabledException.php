<?php declare(strict_types=1);

namespace Symphony\Console\Exceptions;

use \ExtensionManager as SymphonyExtensionManager;
use \Extension as SymphonyExtension;

class ExtensionNotEnabledException extends ConsoleException
{
    public function __construct(string $handle, int $status, $code = 0, \Exception $previous = null)
    {
        switch ($status) {
            case SymphonyExtension::EXTENSION_NOT_INSTALLED:
                $message = "Did you install the %s extension?";
                break;

            case SymphonyExtension::EXTENSION_REQUIRES_UPDATE:
                $message = "%s extension requires updating.";
                break;

            case SymphonyExtension::EXTENSION_NOT_COMPATIBLE:
                $message = "%s extension is not compatible with this version of Symphony CMS.";
                break;

            case SymphonyExtension::EXTENSION_DISABLED:
            default:
                $message = "Did you enable the %s extension?";
                break;
        }

        return parent::__construct(sprintf($message, $handle), $code, $previous);
    }
}
