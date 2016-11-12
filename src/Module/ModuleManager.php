<?php
namespace Skinny\Module;

use ArrayAccess;
use Countable;
use DirectoryIterator;
use Exception;
use RuntimeException;
use Skinny\Core\Configure;
use Skinny\Core\Plugin;
use Skinny\Utility\Inflector;

class ModuleManager implements ArrayAccess, Countable
{
    /**
     * Constant that can be returned by modules to indicate to halt noticing further modules.
     *
     * @var int
     */
    const STOP = -1;

    /**
     * Modules that have priority over other modules.
     *
     * @var array
     */
    protected $priorityList = [];

    /**
     * Loaded modules.
     *
     * @var array
     */
    protected $loadedModules = [];

    /**
     * An argument that should be passed every call.
     *
     * @var array
     */
    protected $prefixArgument = null;

    /**
     * Constructor, loads all Modules in the Module directory.
     *
     * @param array $priorities The modules to load in priority.
     */
    public function __construct(array $priorities = [])
    {
        $this->priorityList = $priorities;
        $files = new DirectoryIterator(MODULE_DIR);
        $this->loadModules($files);

        $plugins = Plugin::loaded();
        if (!empty((array)$plugins)) {
            foreach ($plugins as $plugin) {
                $path = Plugin::classPath($plugin) . 'Module' . DS . 'Modules';
                $files = new DirectoryIterator($path);

                $this->loadModules($files, ['pathDir' => $path, 'plugin' => $plugin]);
            }
        }
    }

    /**
     * Call destructors to unload all modules directly.
     */
    public function __destruct()
    {
        $this->loadedModules = [];
    }

    /**
     * Calls a given method on all modules with the arguments passed to this method.
     * The loop will halt when the method returns the STOP constant, indicating all
     * work is done.
     *
     * @param string $method The method to check.
     * @param array  $arguments The arguments to pass to the function.
     *
     * @return bool
     */
    public function __call($method, array $arguments)
    {
        //Add out predefined prefix argument to the total list.
        if (!is_null($this->prefixArgument)) {
            array_unshift($arguments, $this->prefixArgument);
        }

        foreach ($this->loadedModules as $module) {
            //Check if the module has the method.
            if (!method_exists($module['object'], $method)) {
                continue;
            }

            //Check if we should stop calling modules.
            if (call_user_func_array([$module['object'], $method], $arguments) === self::STOP) {
                break;
            }
        }

        return true;
    }

    /**
     * Argument that should be passed into Modules.
     *
     * @param array $argument The arguments to add.
     *
     * @return bool
     */
    public function addPrefixArgument($argument)
    {
        $this->prefixArgument = $argument;

        return true;
    }

    /**
     * Loads the list of modules in the DirectoryIterator.
     *
     * Options :
     * - pathDir : The full path of the Modules directory. Default to MODULE_DIR.
     * - plugin : The plugin name or false if no plugin. Default to false.
     *
     * @param \DirectoryIterator $files The DirectoryIterator instance.
     * @param array $config The configuration options.
     *
     * @throws \RuntimeException When the class doesn't implement the ModuleInterface.
     *
     * @return string
     */
    protected function loadModules(DirectoryIterator $files, array $config = [])
    {
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if ($file->isDot() || $file->isDir() || $filename[0] == '.') {
                // Ignore hidden files and directories.
                continue;
            } elseif ($file->isFile() && substr($filename, -4) != '.php') {
                continue;
            } else {
                try {
                    $this->load(substr($filename, 0, -4), $config);
                } catch (Exception $e) {
                    throw new RuntimeException(sprintf('Error while loading module : %s', $e->getMessage()));
                }
            }
        }
    }

    /**
     * Check if the module exist in the plugin list. Primary used when we need to load a module and
     * the module doesn't exists in the application itself.
     *
     * @param string $module The module to check.
     *
     * @return array The config needed for the ModuleManager::load() function.
     */
    protected function checkPlugins($module)
    {
        $loadedPlugins = Configure::read('plugins');
        $arr = [];

        if (empty($loadedPlugins) || !is_array($loadedPlugins)) {
            return $arr;
        }

        foreach ($loadedPlugins as $plugin => $pluginPath) {
            $pluginPath = Plugin::classPath($plugin) . 'Module' . DS . 'Modules';
            $files = new DirectoryIterator($pluginPath);

            foreach ($files as $file) {
                $filename = $file->getFilename();

                if ($file->isDot() || $file->isDir() || $filename[0] == '.') {
                    continue;
                } elseif ($file->isFile() && substr($filename, -4) != '.php') {
                    continue;
                } elseif (Inflector::camelize(substr($filename, 0, -4)) !== $module) {
                    continue;
                } else {
                    $arr = ['pathDir' => $pluginPath, 'plugin' => $plugin];

                    return $arr;
                }
            }
        }

        return $arr;
    }

    /**
     * Loads a module into the Framework and prioritize it according to our priority list.
     *
     * Options :
     * - pathDir : The full path of the Modules directory. Default to MODULE_DIR.
     * - plugin : The plugin name or false if no plugin. Default to false.
     *
     * @param string $module Filename of the module we want to load.
     * @param array $config The configuration options.
     *
     * @throws \RuntimeException When the class doesn't implement the ModuleInterface.
     *
     * @return string
     */
    public function load($module, array $config = [])
    {
        $module = Inflector::camelize($module);

        $config += [
            'pathDir' => MODULE_DIR,
            'plugin' => false
        ];

        if (isset($this->loadedModules[$module])) {
            //Return the message AlreadyLoaded.
            return 'AL';
        }

        //Check if the module exist in the plugin list.
        $config = array_merge($config, $this->checkPlugins($module));


        if (!file_exists($config['pathDir'] . DS . $module . '.php')) {
            //Return NotFound.
            return 'NF';
        }

        //Check if this class already exists.
        $path = $config['pathDir'] . DS . $module . '.php';
        $className = Configure::read('App.namespace') . DS . 'Module' . DS . 'Modules' . DS . $module;

        if (Configure::read('debug') === false) {
            require_once $path;
        } else {
            //Here, we load the file's contents first, then use preg_replace() to replace
            //the original class-name with a random one. After that, we create a copy and include it.
            $newClass = $module . '_' . md5(mt_rand() . time());
            $contents = preg_replace(
                "/(class[\s]+?)" . $module . "([\s]+?implements[\s]+?ModuleInterface[\s]+?{)/",
                "\\1" . $newClass . "\\2",
                file_get_contents($path)
            );

            $name = tempnam(TMP_MODULE_DIR, $module . '_');
            file_put_contents($name, $contents);

            require_once $name;
            unlink($name);

            $namespace = ($config['plugin'] !== false) ? $config['plugin'] : Configure::read('App.namespace');
            $className = $namespace . DS . 'Module' . DS . 'Modules' . DS . $newClass;
        }

        $className = str_replace('/', '\\', rtrim($className, '\\'));

        $objectModule = new $className();
        $new = [
            'object' => $objectModule,
            'loaded' => time(),
            'plugin' => ($config['plugin'] !== false) ? true : false,
            'pluginName' => ($config['plugin'] !== false) ? $config['plugin'] : '',
            'pluginPath' => $config['pathDir'],
            'name' => $className,
            'modified' => (isset($contents) ? true : false)
        ];

        //Check if this module implements our default interface.
        if (!$objectModule instanceof ModuleInterface) {
            throw new RuntimeException(
                sprintf('ModuleManager::load() expects "%s" to be an instance of ModuleInterface.', $className)
            );
        }

        //Prioritize.
        if (in_array($module, $this->priorityList)) {
            //So, here we reverse our list of loaded modules, so that prioritized modules will be the last ones,
            //then, we add the current prioritized modules to the array and reverse it again.
            $temp = array_reverse($this->loadedModules, true);
            $temp[$module] = $new;
            $this->loadedModules = array_reverse($temp, true);
        } else {
            $this->loadedModules[$module] = $new;
        }

        //Return the message Loaded.
        return 'L';
    }

    /**
     * Unload a module from the Framework.
     *
     * @param string $module Module to unload.
     *
     * @return string
     */
    public function unload($module)
    {
        $module = Inflector::camelize($module);

        if (!isset($this->loadedModules[$module])) {
            //Return the message AlreadyUnloaded.
            return 'AU';
        }

        //Remove this module, also calling the __destruct method of it.
        $object = $this->loadedModules[$module]['object'];
        unset($object);
        unset($this->loadedModules[$module]);

        //Return the message Unloaded.
        return 'U';
    }

    /**
     * Reloads a module by first calling unload and then load.
     *
     * @param string $module The module to reload.
     *
     * @return string
     */
    public function reload($module)
    {
        $module = Inflector::camelize($module);
        $config = [];

        if (isset($this->loadedModules[$module]) && $this->loadedModules[$module]['plugin'] === true) {
            $config += [
                'pathDir' => $this->loadedModules[$module]['pluginPath'],
                'plugin' => $this->loadedModules[$module]['pluginName']
            ];
        }

        $unload = $this->unload($module);

        if ($unload != "U") {
            return $unload;
        }

        return $this->load($module, $config);
    }

    /**
     * Returns the time when a module was loaded or false if we don't have it.
     *
     * @param string $module The module to check the time.
     *
     * @return false|int
     */
    public function timeLoaded($module)
    {
        $module = Inflector::camelize($module);

        if (!isset($this->loadedModules[$module])) {
            return false;
        }

        return $this->loadedModules[$module]['loaded'];
    }

    /**
     * Returns if a module has been modified or -1 if we do not have it
     *
     * @param string $module The module to check.
     *
     * @return bool|int
     */
    public function isModified($module)
    {
        $module = Inflector::camelize($module);

        if (!isset($this->loadedModules[$module])) {
            return -1;
        }

        return $this->loadedModules[$module]['modified'];
    }

    /**
     * Returns an array with names of all loaded modules, sorted on their priority.
     *
     * @return array
     */
    public function getLoadedModules()
    {
        return array_keys($this->loadedModules);
    }

    /**
     * Returns the numbers of modules loaded.
     *
     * @return int
     */
    public function count()
    {
        return count($this->loadedModules);
    }

    /**
     * Returns instance of a loaded module if we have it, or false if we don't have it.
     *
     * @param string $module The module to get.
     *
     * @return bool|object
     */
    public function offsetGet($module)
    {
        $module = Inflector::camelize($module);

        if (!isset($this->loadedModules[$module])) {
            return false;
        }

        return $this->loadedModules[$module]['object'];
    }

    /**
     * Check if we have loaded a certain module.
     *
     * @param string $module The module to check.
     *
     * @return bool
     */
    public function offsetExists($module)
    {
        $module = Inflector::camelize($module);

        return isset($this->loadedModules[$module]);
    }

    /**
     * Creates a new Module in our list.
     *
     * @param string $offset The offset of the moddule.
     * @param object $module The module to create.
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function offsetSet($offset, $module)
    {
        if (!$module instanceof ModuleInterface) {
            throw new RuntimeException(
                sprintf('ModuleManager::offsetSet() expects "%s" to be an instance of ModuleInterface.', $module)
            );
        }

        $newModule = [
            'object' => $module,
            'loaded' => time(),
            'name' => get_class($module),
            'modified' => false
        ];

        if (in_array($offset, $this->priorityList)) {
            $temp = array_reverse($this->loadedModules, true);
            $temp[$offset] = $newModule;
            $this->loadedModules = array_reverse($temp, true);
        } else {
            $this->loadedModules[$offset] = $newModule;
        }

        return true;
    }

    /**
     * Unload a Module, this is basically the same as unload().
     *
     * @param string $module The module to unlod.
     *
     * @return bool
     */
    public function offsetUnset($module)
    {
        if (!isset($this->loadedModules[$module])) {
            return true;
        }

        unset($this->loadedModules[$module]);

        return true;
    }
}
