<?php

namespace Skinny\Configure;

use Skinny\Configure\Exception\Exception;
use Skinny\Utility\Hash;

class Configure
{
    /**
     * Extension of configuration file.
     *
     * @var const
     */
    const EXT = '.php';

    /**
     * Array of values currently stored in Configure.
     *
     * @var array
     */
    protected static $_values = [
        'debug' => 0
    ];

    /**
     * Flag to track whether or not ini_set exists.
     *
     * @return void
     */
    protected static $_hasIniSet = null;

    /**
     * Used to store a dynamic variable in Configure.
     *
     * Usage:
     * ```
     * Configure::write('One.key1', 'value of the Configure::One[key1]');
     * Configure::write(['One.key1' => 'value of the Configure::One[key1]']);
     * Configure::write('One', [
     * 'key1' => 'value of the Configure::One[key1]',
     * 'key2' => 'value of the Configure::One[key2]'
     * ]);
     *
     * Configure::write([
     * 'One.key1' => 'value of the Configure::One[key1]',
     * 'One.key2' => 'value of the Configure::One[key2]'
     * ]);
     * ```
     *
     * @param string|array $config The key to write, can be a dot notation value.
     * Alternatively can be an array containing key(s) and value(s).
     * @param mixed $value Value to set for var.
     *
     * @return bool True if write was successful.
     */
    public static function write($config, $value = null)
    {
        if (!is_array($config)) {
            $config = [$config => $value];
        }
        foreach ($config as $name => $value) {
            static::$_values = Hash::insert(static::$_values, $name, $value);
        }
        if (isset($config['debug'])) {
            if (static::$_hasIniSet === null) {
                static::$_hasIniSet = function_exists('ini_set');
            }
            if (static::$_hasIniSet) {
                ini_set('display_errors', $config['debug'] ? 1 : 0);
            }
        }

        return true;
    }

    /**
     * Used to read information stored in Configure. It's not
     * possible to store `null` values in Configure.
     *
     * Usage:
     * ```
     * Configure::read('Name'); will return all values for Name
     * Configure::read('Name.key'); will return only the value of Configure::Name[key]
     * ```
     *
     * @param string $var Variable to obtain. Use '.' to access array elements.
     *
     * @return mixed value stored in configure, or null.
     */
    public static function read($var = null)
    {
        if ($var === null) {
            return static::$_values;
        }

        return Hash::get(static::$_values, $var);
    }

    /**
     * Returns true if given variable is set in Configure.
     *
     * @param string $var Variable name to check for.
     *
     * @return bool True if variable is there.
     */
    public static function check($var)
    {
        if (empty($var)) {
            return false;
        }

        return Hash::get(static::$_values, $var) !== null;
    }

    /**
     * Used to delete a variable from Configure.
     *
     * Usage:
     * ```
     * Configure::delete('Name'); will delete the entire Configure::Name
     * Configure::delete('Name.key'); will delete only the Configure::Name[key]
     * ```
     *
     * @param string $var the var to be deleted.
     *
     * @return void
     */
    public static function delete($var)
    {
        static::$_values = Hash::remove(static::$_values, $var);
    }

    /**
     * Used to read and delete a variable from Configure.
     *
     * This is primarily used during bootstrapping to move configuration data
     * out of configure into the various other classes in the Bot.
     *
     * @param string $var The key to read and remove.
     *
     * @return array|null
     */
    public static function consume($var)
    {
        $simple = strpos($var, '.') === false;
        if ($simple && !isset(static::$_values[$var])) {
            return null;
        }
        if ($simple) {
            $value = static::$_values[$var];
            unset(static::$_values[$var]);

            return $value;
        }
        $value = Hash::get(static::$_values, $var);
        static::$_values = Hash::remove(static::$_values, $var);

        return $value;
    }

    /**
     * Loads stored configuration information from a resource. You can add
     * config file resource engines with `Configure::config()`.
     *
     * Loaded configuration information will be merged with the current
     * runtime configuration. You can load configuration files from plugins
     * by preceding the filename with the plugin name.
     *
     * `Configure::load('Users.user')`
     *
     * Would load the 'user' config file using the default config engine. You can load
     * app config files by giving the name of the resource you want loaded.
     *
     * `Configure::load('setup');`
     *
     * @param string $key name of configuration resource to load.
     * @param bool $merge if config files should be merged instead of simply overridden
     *
     * @return bool if file not found, void if load successful.
     */
    public static function load($key, $merge = true)
    {
        $file = static::_getFilePath($key);

        $return = include $file;
        if (!is_array($return)) {
            throw new Exception(sprintf('Config file "%s" did not return an array', $key . static::EXT));
        }

        $values = $return;
        if ($merge) {
            $values = Hash::merge(static::$_values, $values);
        }

        return static::write($values);
    }

    /**
     * Return the file path.
     *
     * @param string $key The file.
     *
     * @return string
     */
    protected static function _getFilePath($key)
    {
        return CONFIG . $key . static::EXT;
    }

    /**
     * Dump data currently in Configure into $key. The serialization format
     * will be an array.
     *
     * ### Usage
     *
     * Save all data in Configure to the file `my_config`:
     *
     * `Configure::dump('my_config');`
     *
     * Save only the error handling configuration:
     *
     * `Configure::dump('error', ['Error', 'Exception'];`
     *
     * @param string $key The identifier to create in the config adapter.
     * This could be a filename or a cache key depending on the adapter being used.
     * @param array $keys The name of the top-level keys you want to dump.
     * This allows you save only some data stored in Configure.
     *
     * @return bool On success or error.
     */
    public static function dump($key, $keys = [])
    {
        $values = static::$_values;

        if (!empty($keys) && is_array($keys)) {
            $values = array_intersect_key($values, array_flip($keys));
        }
        $contents = '<?php' . "\n" . 'return ' . var_export($values, true) . ';';

        $filename = static::_getFilePath($key);

        return (bool)file_put_contents($filename, $contents);
    }

    /**
     * Used to determine the current version of the Bot.
     *
     * Usage `Configure::version();`
     *
     * @return string Current version of the Bot.
     */
    public static function version()
    {
        if (!isset(static::$_values['Bot']['version'])) {
            require ROOT . DS . 'config' . DS . 'version.php';
            static::write($config);
        }

        return static::$_values['Bot']['version'];
    }

    /**
     * Clear all values stored in Configure.
     *
     * @return bool success.
     */
    public static function clear()
    {
        static::$_values = [];

        return true;
    }
}
