<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Skinny\Core;

use Skinny\Core\Configure\ConfigEngineInterface;
use Skinny\Core\Configure\Engine\PhpConfig;
use Skinny\Core\Exception\Exception;
use Skinny\Utility\Hash;
use RuntimeException;

class Configure
{

    /**
     * Array of values currently stored in Configure.
     *
     * @var array
     */
    protected static $values = [
        'debug' => false
    ];

    /**
     * Configured engine classes, used to load config files from resources
     *
     * @var array
     * @see \Skinny\Core\Configure::load()
     */
    protected static $engines = [];

    /**
     * Flag to track whether or not ini_set exists.
     *
     * @return void
     */
    protected static $hasIniSet = null;

    /**
     * Used to store a dynamic variable in Configure.
     *
     * Usage:
     * ```
     * Configure::write('One.key1', 'value of the Configure::One[key1]');
     * Configure::write(['One.key1' => 'value of the Configure::One[key1]']);
     * Configure::write('One', [
     *     'key1' => 'value of the Configure::One[key1]',
     *     'key2' => 'value of the Configure::One[key2]'
     * ]);
     *
     * Configure::write([
     *     'One.key1' => 'value of the Configure::One[key1]',
     *     'One.key2' => 'value of the Configure::One[key2]'
     * ]);
     * ```
     *
     * @param string|array $config The key to write, can be a dot notation value.
     * Alternatively can be an array containing key(s) and value(s).
     * @param mixed $value Value to set for var.
     *
     * @return bool True if write was successful
     */
    public static function write($config, $value = null)
    {
        if (!is_array($config)) {
            $config = [$config => $value];
        }

        foreach ($config as $name => $value) {
            static::$values = Hash::insert(static::$values, $name, $value);
        }

        if (isset($config['debug'])) {
            if (static::$hasIniSet === null) {
                static::$hasIniSet = function_exists('ini_set');
            }
            if (static::$hasIniSet) {
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
     * @param string|null $var Variable to obtain. Use '.' to access array elements.
     *
     * @return mixed Value stored in configure, or null.
     */
    public static function read($var = null)
    {
        if ($var === null) {
            return static::$values;
        }

        return Hash::get(static::$values, $var);
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

        return static::read($var) !== null;
    }

    /**
     * Used to get information stored in Configure. It's not
     * possible to store `null` values in Configure.
     *
     * Acts as a wrapper around Configure::read() and Configure::check().
     * The configure key/value pair fetched via this method is expected to exist.
     * In case it does not an exception will be thrown.
     *
     * Usage:
     * ```
     * Configure::readOrFail('Name'); will return all values for Name
     * Configure::readOrFail('Name.key'); will return only the value of Configure::Name[key]
     * ```
     *
     * @param string $var Variable to obtain. Use '.' to access array elements.
     *
     * @return mixed Value stored in configure.
     *
     * @throws \RuntimeException if the requested configuration is not set.
     */
    public static function readOrFail($var)
    {
        if (static::check($var) === false) {
            throw new RuntimeException(sprintf('Expected configuration key "%s" not found.', $var));
        }

        return static::read($var);
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
     * @param string $var The var to be deleted.
     *
     * @return void
     */
    public static function delete($var)
    {
        static::$values = Hash::remove(static::$values, $var);
    }

    /**
     * Used to read and delete a variable from Configure.
     *
     * @param string $var The key to read and remove.
     *
     * @return array|null
     */
    public static function consume($var)
    {
        if (strpos($var, '.') === false) {
            if (!isset(static::$values[$var])) {
                return null;
            }
            $value = static::$values[$var];
            unset(static::$values[$var]);

            return $value;
        }
        $value = Hash::get(static::$values, $var);
        static::delete($var);

        return $value;
    }

    /**
     * Add a new engine to Configure. Engines allow you to read configuration
     * files in various formats/storage locations. You can also implement your
     * own engine classes in your application.
     *
     * To add a new engine to Configure:
     *
     * ```
     * Configure::config('ini', new IniConfig());
     * ```
     *
     * @param string $name The name of the engine being configured. This alias is used later to
     *   read values from a specific engine.
     * @param \Skinny\Core\Configure\ConfigEngineInterface $engine The engine to append.
     *
     * @return void
     */
    public static function config($name, ConfigEngineInterface $engine)
    {
        static::$engines[$name] = $engine;
    }

    /**
     * Gets the names of the configured Engine objects.
     *
     * @param string|null $name Engine name.
     *
     * @return array Array of the configured Engine objects.
     */
    public static function configured($name = null)
    {
        if ($name !== null) {
            return isset(static::$engines[$name]);
        }

        return array_keys(static::$engines);
    }

    /**
     * Remove a configured engine. This will unset the engine
     * and make any future attempts to use it cause an Exception.
     *
     * @param string $name Name of the engine to drop.
     *
     * @return bool Success
     */
    public static function drop($name)
    {
        if (!isset(static::$engines[$name])) {
            return false;
        }
        unset(static::$engines[$name]);

        return true;
    }

    /**
     * Loads stored configuration information from a resource. You can add
     * config file resource engines with `Configure::config()`.
     *
     * Loaded configuration information will be merged with the current
     * runtime configuration. You can load configuration files from plugins
     * by preceding the filename with the plugin name.
     *
     * `Configure::load('Users.user', 'default')`
     *
     * Would load the 'user' config file using the default config engine. You can load
     * app config files by giving the name of the resource you want loaded.
     *
     * ```
     * Configure::load('setup', 'default');
     * ```
     *
     * If using `default` config and no engine has been configured for it yet,
     * one will be automatically created using PhpConfig.
     *
     * @param string $key Name of configuration resource to load.
     * @param string $config Name of the configured engine to use to read the resource identified by $key.
     * @param bool $merge If config files should be merged instead of simply overridden.
     *
     * @return bool False if file not found, true if load successful.
     */
    public static function load($key, $config = 'default', $merge = true)
    {
        $engine = static::getEngine($config);
        if (!$engine) {
            return false;
        }
        $values = $engine->read($key);

        if ($merge) {
            $values = Hash::merge(static::$values, $values);
        }

        return static::write($values);
    }

    /**
     * Dump data currently in Configure into $key. The serialization format
     * is decided by the config engine attached as $config. For example, if the
     * 'default' adapter is a PhpConfig, the generated file will be a PHP
     * configuration file loadable by the PhpConfig.
     *
     * ### Usage
     *
     * Given that the 'default' engine is an instance of PhpConfig.
     * Save all data in Configure to the file `my_config.php`:
     *
     * ```
     * Configure::dump('my_config', 'default');
     * ```
     *
     * Save only the error handling configuration:
     *
     * ```
     * Configure::dump('error', 'default', ['Error', 'Exception'];
     * ```
     *
     * @param string $key The identifier to create in the config adapter.
     *   This could be a filename or a cache key depending on the adapter being used.
     * @param string $config The name of the configured adapter to dump data with.
     * @param array $keys The name of the top-level keys you want to dump.
     *   This allows you save only some data stored in Configure.
     *
     * @return bool Success
     *
     * @throws \Skinny\Core\Exception\Exception If the adapter does not implement a `dump` method.
     */
    public static function dump($key, $config = 'default', $keys = [])
    {
        $engine = static::getEngine($config);
        if (!$engine) {
            throw new Exception(sprintf('There is no "%s" config engine.', $config));
        }
        $values = static::$values;
        if (!empty($keys) && is_array($keys)) {
            $values = array_intersect_key($values, array_flip($keys));
        }

        return (bool)$engine->dump($key, $values);
    }

    /**
     * Get the configured engine. Internally used by `Configure::load()` and `Configure::dump()`
     * Will create new PhpConfig for default if not configured yet.
     *
     * @param string $config The name of the configured adapter
     *
     * @return \Skinny\Core\Configure\ConfigEngineInterface|false Engine instance or false.
     */
    protected static function getEngine($config)
    {
        if (!isset(static::$engines[$config])) {
            if ($config !== 'default') {
                return false;
            }
            static::config($config, new PhpConfig());
        }

        return static::$engines[$config];
    }

    /**
     * Used to determine the current version of Skinny.
     *
     * Usage
     * ```
     * Configure::version();
     * ```
     *
     * @return string Current version of Skinny.
     */
    public static function version()
    {
        if (!isset(static::$values['Skinny']['version'])) {
            $config = require SKINNY_PATH . 'config' . DS . 'version.php';
            static::write($config);
        }

        return static::$values['Skinny']['version'];
    }

    /**
     * Verifies that the application's token value has been changed from the default value.
     *
     * @return void
     *
     * @throws \RuntimeException When the Discord.token configuration is not set.
     */
    public static function checkTokenKey()
    {
        if (Configure::read('Discord.token') === 'insert-your-token-here') {
            throw new RuntimeException(sprintf('Please change the value of %s in %s to a valid token.',
                '\'Discord.token\'', ROOT . '/config/config.php'));
        }
    }

    /**
     * Clear all values stored in Configure.
     *
     * @return bool success.
     */
    public static function clear()
    {
        static::$values = [];

        return true;
    }
}
