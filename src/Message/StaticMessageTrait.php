<?php
namespace Skinny\Message;

use InvalidArgumentException;

/**
 * A trait that provides a set of static methods to manage Message class.
 */
trait StaticMessageTrait
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
     * Configuration sets.
     *
     * @var array
     */
    protected static $config = [];

    /**
     * This method can be used to define configuration.
     *
     * ### Usage
     *
     * Writing config data:
     *
     * ```
     * Message::config('message', 'hi');
     * ```
     *
     * Writing multiple config data at once:
     *
     * ```
     * Message::config(['message' => 'hi', 'commandCode' => '!']);
     * ```
     *
     * @param string|array $key The name of the configuration to read, or an array of multiple configs.
     *
     * @return null
     *
     * @throws \InvalidArgumentException If the key is not a string or an array.
     */
    public static function config($key, $value = null)
    {
        if (is_string($key)) {
            static::$config[$key] = $value;

            return;
        }

        if (!is_array($key)) {
            throw new InvalidArgumentException('Only string and array can be passed to config.');
        }

        foreach ($key as $name => $value) {
            static::$config[$name] = $value;
        }

        return;
    }

    /**
     * Read a config variable or all the config.
     *
     * ### Usage
     *
     * Reading config with key:
     *
     * ```
     * Message::read('message');
     * ```
     *
     * Reading all the config:
     *
     * ```
     * Message::read();
     * ```
     *
     * @param string|null The key to read the configuration.
     *
     * @return mixed
     */
    public static function read($key = null)
    {
        if ($key === null) {
            return static::$config;
        }

        return isset(static::$config[$key]) ? static::$config[$key] : null;
    }

    /**
     * Reset the config. If default is true, reset to the default values.
     *
     * @param bool $default True to reset the config to the default value.
     *
     * @return void|null Null when reseting the config tot he default values.
     */
    public static function resetConfig($default = true)
    {
        if ($default) {
            static::$config = static::$defaultConfig;

            return;
        }

        static::$config = [];
    }
}
