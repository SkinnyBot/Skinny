<?php
namespace Skinny\Core\Exception;

class MissingPluginException extends Exception
{

    protected $_messageTemplate = 'Plugin %s could not be found.';
}
