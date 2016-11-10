<?php
namespace Skinny\Utility;

use Skinny\Configure\Configure;

class Command
{
    /**
     * Return the syntax of a command formated.
     *
     * @param array $message The message array.
     *
     * @return string The syntax formated.
     */
    public static function syntax($message)
    {
        return 'Not enough parameters given. Syntax: `' . Configure::read('Command.prefix') .
            Configure::read('Commands')[$message['command']]['syntax'] . '`';
    }

    /**
     * Return the syntax of a unknow command formated.
     *
     * @param array $message The message array.
     *
     * @return string The syntax formated.
     */
    public static function unknown($message)
    {
        return 'Unknown command. Syntax: `' . Configure::read('Commands')[$message['command']]['syntax'] . '`';
    }
}
