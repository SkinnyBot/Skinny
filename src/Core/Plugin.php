<?php
namespace Skinny\Core;

use DirectoryIterator;
use Skinny\Core\Configure;
use Skinny\Core\Exception\MissingPluginException;

class Plugin
{

    /**
     * Holds a list of all loaded plugins and their configuration
     *
     * @var array
     */
    protected static $plugins = [];

    /**
     * Loads a plugin and optionally loads bootstrap files.
     *
     * ### Examples:
     *
     * `Plugin::load('Basic')`
     *
     * Will load the Basic plugin and will not load the bootstrap file.
     *
     * `Plugin::load('Basic', ['bootstrap' => true])`
     *
     * Will load the bootstrap.php file.
     *
     * It is also possible to load multiple plugins at once. Examples:
     *
     * `Plugin::load(['Basic', 'Developer'])`
     *
     * Will load the Basic and Developer plugins.
     *
     * `Plugin::load(['Basic', 'Developer'], ['bootstrap' => true])`
     *
     * Will load bootstrap file for both plugins
     *
     *
     * ### Configuration options
     *
     * - `bootstrap` - array - Whether or not you want the $plugin/config/bootstrap.php file loaded.
     * - `ignoreMissing` - boolean - Set to true to ignore missing bootstrap files.
     * - `path` - string - The path the plugin can be found on. If empty the default plugin path will be used.
     * - `classBase` - string - The path relative to `path` which contains the folders with class files.
     * Defaults to "src".
     *
     * @param string|array $plugin Name of the plugin to be loaded in CamelCase format or array or plugins to load.
     * @param array $config Configuration options for the plugin.
     *
     * @throws \Skinny\Core\Exception\MissingPluginException If the folder for the plugin to be loaded is not found.
     *
     * @return void
     */
    public static function load($plugin, array $config = [])
    {
        if (is_array($plugin)) {
            foreach ($plugin as $name => $conf) {
                list($name, $conf) = (is_numeric($name)) ? [$conf, $config] : [$name, $conf];
                static::load($name, $conf);
            }

            return;
        }

        static::loadConfig();

        $config += [
            'bootstrap' => false,
            'classBase' => 'src',
            'ignoreMissing' => false
        ];

        if (!isset($config['path'])) {
            $config['path'] = Configure::read('plugins.' . $plugin);
        }

        if (empty($config['path'])) {
            $paths = (array)Configure::read('App.paths.plugins');
            $pluginPath = str_replace('/', DIRECTORY_SEPARATOR, $plugin);
            foreach ($paths as $path) {
                if (is_dir($path . $pluginPath)) {
                    $config['path'] = $path . $pluginPath . DIRECTORY_SEPARATOR;
                    break;
                }
            }
        }

        if (empty($config['path'])) {
            throw new MissingPluginException(['plugin' => $plugin]);
        }

        $config['classPath'] = $config['path'] . $config['classBase'] . DIRECTORY_SEPARATOR;
        if (!isset($config['configPath'])) {
            $config['configPath'] = $config['path'] . 'config' . DIRECTORY_SEPARATOR;
        }

        static::$plugins[$plugin] = $config;


        if ($config['bootstrap'] === true) {
            static::bootstrap($plugin);
        }
    }

    /**
     * Load the plugin path configuration file.
     *
     * @return void
     */
    protected static function loadConfig()
    {
        if (Configure::check('plugins')) {
            return;
        }
        $vendorFile = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'skinny-plugins.php';
        if (!file_exists($vendorFile)) {
            $vendorFile = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'skinny-plugins.php';
            if (!file_exists($vendorFile)) {
                Configure::write(['plugins' => []]);

                return;
            }
        }

        $config = require $vendorFile;
        Configure::write($config);
    }

    /**
     * Will load all the plugins located in the default plugin folder.
     *
     * If passed an options array, it will be used as a common default for all plugins to be loaded
     * It is possible to set specific defaults for each plugins in the options array. Examples:
     *
     * ```
     *  Plugin::loadAll([
     *      ['bootstrap' => true],
     *      'Basic' => ['bootstrap' => false],
     *  ]);
     * ```
     *
     * The above example will load the bootstrap file for all plugins,
     * but for Basic it will not load the bootstrap file.
     *
     * If a plugin has been loaded already, it will not be reloaded by loadAll().
     *
     * @param array $options Options.
     *
     * @return void
     */
    public static function loadAll(array $options = [])
    {
        static::loadConfig();
        $plugins = [];
        foreach ((array)Configure::read('App.paths.plugins') as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $dir = new DirectoryIterator($path);
            foreach ($dir as $path) {
                if ($path->isDir() && !$path->isDot()) {
                    $plugins[] = $path->getBasename();
                }
            }
        }
        if (Configure::check('plugins')) {
            $plugins = array_merge($plugins, array_keys(Configure::read('plugins')));
            $plugins = array_unique($plugins);
        }

        foreach ($plugins as $p) {
            $opts = isset($options[$p]) ? $options[$p] : null;
            if ($opts === null && isset($options[0])) {
                $opts = $options[0];
            }
            if (isset(static::$plugins[$p])) {
                continue;
            }
            static::load($p, (array)$opts);
        }
    }

    /**
     * Returns the filesystem path for a plugin.
     *
     * @param string $plugin Name of the plugin in CamelCase format.
     *
     * @return string Path to the plugin folder.
     *
     * @throws \Skinny\Core\Exception\MissingPluginException if the folder for plugin was not found or
     * plugin has not been loaded
     */
    public static function path($plugin)
    {
        if (empty(static::$plugins[$plugin])) {
            throw new MissingPluginException(['plugin' => $plugin]);
        }

        return static::$plugins[$plugin]['path'];
    }

    /**
     * Returns the filesystem path for plugin's folder containing class folders.
     *
     * @param string $plugin name of the plugin in CamelCase format.
     *
     * @return string Path to the plugin folder container class folders.
     *
     * @throws \Skinny\Core\Exception\MissingPluginException If plugin has not been loaded.
     */
    public static function classPath($plugin)
    {
        if (empty(static::$plugins[$plugin])) {
            throw new MissingPluginException(['plugin' => $plugin]);
        }

        return static::$plugins[$plugin]['classPath'];
    }

    /**
     * Returns the filesystem path for plugin's folder containing config files.
     *
     * @param string $plugin name of the plugin in CamelCase format.
     *
     * @return string Path to the plugin folder container config files.
     *
     * @throws \Skinny\Core\Exception\MissingPluginException If plugin has not been loaded.
     */
    public static function configPath($plugin)
    {
        if (empty(static::$plugins[$plugin])) {
            throw new MissingPluginException(['plugin' => $plugin]);
        }

        return static::$plugins[$plugin]['configPath'];
    }

    /**
     * Loads the bootstrapping files for a plugin, or calls the initialization setup in the configuration.
     *
     * @param string $plugin Name of the plugin.
     *
     * @return mixed
     *
     * @see \Skinny\Core\Plugin::load() for examples of bootstrap configuration
     */
    public static function bootstrap($plugin)
    {
        $config = static::$plugins[$plugin];
        if ($config['bootstrap'] === false) {
            return false;
        }
        if ($config['bootstrap'] === true) {
            return static::includeFile(
                $config['configPath'] . 'bootstrap.php',
                $config['ignoreMissing']
            );
        }
    }

    /**
     * Returns true if the plugin $plugin is already loaded
     * If plugin is null, it will return a list of all loaded plugins
     *
     * @param string|null $plugin Plugin name.
     *
     * @return bool|array Boolean true if $plugin is already loaded.
     *   If $plugin is null, returns a list of plugins that have been loaded.
     */
    public static function loaded($plugin = null)
    {
        if ($plugin !== null) {
            return isset(static::$plugins[$plugin]);
        }
        $return = array_keys(static::$plugins);
        sort($return);

        return $return;
    }

    /**
     * Forgets a loaded plugin or all of them if first parameter is null.
     *
     * @param string|null $plugin Name of the plugin to forget.
     *
     * @return void
     */
    public static function unload($plugin = null)
    {
        if ($plugin === null) {
            static::$plugins = [];
        } else {
            unset(static::$plugins[$plugin]);
        }
    }

    /**
     * Include file, ignoring include error if needed if file is missing.
     *
     * @param string $file File to include.
     * @param bool $ignoreMissing Whether to ignore include error for missing files.
     *
     * @return mixed
     */
    protected static function includeFile($file, $ignoreMissing = false)
    {
        if ($ignoreMissing && !is_file($file)) {
            return false;
        }

        return include $file;
    }
}
