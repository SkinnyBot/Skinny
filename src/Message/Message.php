<?php
namespace Skinny\Message;

use Skinny\Singleton\Singleton;

class Message extends Singleton
{
    /**
     * The default configuration.
     *
     * @var array
     */
    protected static $defaultConfig = [
        'raw' => '',
        'parts' => [],
        'command' => '',
        'message' => '',
        'commandCode' => '',
        'arguments' => []
    ];

    /**
     * The data of the message.
     *
     * @var array
     */
    public static $data = [];

    /**
     * Set the data.
     *
     * @param string $message The message to parse
     *
     * @return array The data.
     */
    public static function setData($message)
    {
        //Set the default values before processing.
        static::setDefaultValues();

        //Check if we have a Message.
        if (!empty($message)) {
            static::setValues([
                'raw' => $message,
                'parts' => explode(chr(32), trim($message), 2)
            ]);
            static::setValues([
                'commandCode' => substr(static::$data['parts'][0], 0, 1),
                'command' => substr(static::$data['parts'][0], 1)
            ]);

            //There are more than one word in the message.
            if (count(static::$data['parts']) > 1) {
                static::setValues([
                    'message' => static::$data['parts'][1],
                    'arguments' => explode(chr(32), static::$data['parts'][1])
                ]);
            }

            //Put the command and all the arguments in lowercase.
            static::setValues([
                'command' => strtolower(static::$data['command']),
                'arguments' => array_map('strtolower', static::$data['arguments'])
            ]);
        }

        return static::$data;
    }

    /**
     * Set a key and value in the information array.
     *
     * @param string $key The key to add.
     * @param string $value The value to add with the key.
     *
     * @return void
     */
    public static function setValue($key, $value)
    {
        static::$data[$key] = $value;
    }
    /**
     * Set values in the information array.
     *
     * @param array $values The values to add in the data array.
     *
     * @return void
     */
    public static function setValues($values = [])
    {
        foreach ($values as $key => $value) {
            static::$data[$key] = $value;
        }
    }

    /**
     * Reset the default to the default value.
     *
     * @return void
     */
    protected static function setDefaultValues()
    {
        static::$data = static::$defaultConfig;
    }
}
