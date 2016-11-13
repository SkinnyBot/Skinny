<?php
namespace Skinny\Module\Exception;

use Skinny\Core\Exception\Exception;

class NotImplementedException extends Exception
{
    protected $messageTemplate = '%s expects "%s" to be an instance of ModuleInterface.';
}
