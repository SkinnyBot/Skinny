<?php
namespace Skinny\Core\Exception;

class MissingPluginException extends Exception
{
    protected $messageTemplate = 'Plugin %s could not be found.';
}
