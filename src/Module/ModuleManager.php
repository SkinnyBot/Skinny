<?php
namespace Skinny\Module;

use ArrayAccess;
use Countable;
use DirectoryIterator;
use Exception;
use RuntimeException;
use Skinny\Configure\Configure;
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
    protected $_priorityList = [];

    /**
     * Loaded modules.
     *
     * @var array
     */
    protected $_loadedModules = [];

    /**
     * An argument that should be passed every call.
     *
     * @var array
     */
    protected $_prefixArgument = null;

    /**
     * Constructor, loads all Modules in the Module directory.
     *
     * @param array $priorities The modules to load in priority.
     */
    public function __construct(array $priorities = [])
    {
        $this->_priorityList = $priorities;
        $files = new DirectoryIterator(MODULE_DIR);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            if ($file->isDot() || $file->isDir() || $filename[0] == '.') {
                // Ignore hidden files and directories.
                continue;
            } elseif ($file->isFile() && substr($filename, -4) != '.php') {
                continue;
            } else {
                try {
                    $this->load(substr($filename, 0, -4));
                } catch (Exception $e) {
                    throw new RuntimeException(sprintf('Error while loading module : %s', $e->getMessage()));
                }
            }
        }
    }

    /**
     * Call destructors to unload all modules directly.
     */
    public function __destruct()
    {
        $this->_loadedModules = [];
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
        $this->_prefixArgument = $argument;

        return true;
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
        if (!is_null($this->_prefixArgument)) {
            array_unshift($arguments, $this->_prefixArgument);
        }

        foreach ($this->_loadedModules as $module) {
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
     * Loads a module into the Framework and prioritize it according to our priority list.
     *
     * @param string $module Filename in the MODULE_DIR we want to load.
     *
     * @throws \RuntimeException When the class doesn't implement the ModuleInterface.
     *
     * @return string
     */
    public function load($module)
    {
        $module = Inflector::camelize($module);

        if (isset($this->_loadedModules[$module])) {
            //Return the message AlreadyLoaded.
            return 'AL';
        } elseif (!file_exists(MODULE_DIR . DS . $module . '.php')) {
            //Return NotFound.
            return 'NF';
        }

        //Check if this class already exists.
        $path = MODULE_DIR . DS . $module . '.php';
        $className = Configure::read('App.namespace') . DS . 'Module' . DS . 'Modules' . DS . $module;

        if (Configure::read('debug') === false) {
            require_once $path;
        } else {
            //Here, we load the file's contents first, then use preg_replace() to replace the original class-name with a random one.
            //After that, we create a copy and include it.
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

            $className = Configure::read('App.namespace') . DS . 'Module' . DS . 'Modules' . DS . $newClass;
        }

        $className = str_replace('/', '\\', rtrim($className, '\\'));

        $objectModule = new $className();
        $new = [
            'object' => $objectModule,
            'loaded' => time(),
            'name' => $className,
            'modified' => (isset($contents) ? true : false)
        ];

        //Check if this module implements our default interface.
        if (!$objectModule instanceof ModuleInterface) {
            throw new RuntimeException(sprintf('ModuleManager::load() expects "%s" to be an instance of ModuleInterface.', $className));
        }

        //Prioritize.
        if (in_array($module, $this->_priorityList)) {
            //So, here we reverse our list of loaded modules, so that prioritized modules will be the last ones,
            //then, we add the current prioritized modules to the array and reverse it again.
            $temp = array_reverse($this->_loadedModules, true);
            $temp[$module] = $new;
            $this->_loadedModules = array_reverse($temp, true);
        } else {
            $this->_loadedModules[$module] = $new;
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

        if (!isset($this->_loadedModules[$module])) {
            //Return the message AlreadyUnloaded.
            return 'AU';
        }

        //Remove this module, also calling the __destruct method of it.
        $object = $this->_loadedModules[$module]['object'];
        unset($object);
        unset($this->_loadedModules[$module]);

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

        $unload = $this->unload($module);

        if ($unload != "U") {
            return $unload;
        }

        return $this->load($module);
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

        if (!isset($this->_loadedModules[$module])) {
            return false;
        }

        return $this->_loadedModules[$module]['loaded'];
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

        if (!isset($this->_loadedModules[$module])) {
            return -1;
        }

        return $this->_loadedModules[$module]['modified'];
    }

    /**
     * Returns an array with names of all loaded modules, sorted on their priority.
     *
     * @return array
     */
    public function getLoadedModules()
    {
        return array_keys($this->_loadedModules);
    }

    /**
     * Returns the numbers of modules loaded.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_loadedModules);
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

        if (!isset($this->_loadedModules[$module])) {
            return false;
        }

        return $this->_loadedModules[$module]['object'];
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

        return isset($this->_loadedModules[$module]);
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
            throw new \RuntimeException(sprintf('ModuleManager::offsetSet() expects "%s" to be an instance of ModuleInterface.', $module));
        }

        $newModule = [
            'object' => $module,
            'loaded' => time(),
            'name' => get_class($module),
            'modified' => false
        ];

        if (in_array($offset, $this->_priorityList)) {
            $temp = array_reverse($this->_loadedModules, true);
            $temp[$offset] = $newModule;
            $this->_loadedModules = array_reverse($temp, true);
        } else {
            $this->_loadedModules[$offset] = $newModule;
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
        if (!isset($this->_loadedModules[$module])) {
            return true;
        }

        unset($this->_loadedModules[$module]);

        return true;
    }
}
