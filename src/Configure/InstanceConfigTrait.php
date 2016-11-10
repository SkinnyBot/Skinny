<?php
namespace Skinny\Configure;

use Skinny\Configure\Configure\Exception\Exception;
use Skinny\Utility\Hash;

/**
 * A trait for reading and writing instance config.
 *
 * Implementing objects are expected to declare a `$_defaultConfig` property.
 */
trait InstanceConfigTrait
{
    /**
     * Runtime config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Whether the config property has already been configured with defaults.
     *
     * @var bool
     */
    protected $configInitialized = false;

    /**
     * ### Usage
     *
     * Reading the whole config:
     *
     * `$this->config();`
     *
     * Reading a specific value:
     *
     * `$this->config('key');`
     *
     * Reading a nested value:
     *
     * `$this->config('some.nested.key');`
     *
     * Setting a specific value:
     *
     * `$this->config('key', $value);`
     *
     * Setting a nested value:
     *
     * `$this->config('some.nested.key', $value);`
     *
     * Updating multiple config settings at the same time:
     *
     * `$this->config(['one' => 'value', 'another' => 'value']);`
     *
     * @param string|array|null $key The key to get/set, or a complete array of configs.
     * @param mixed|null $value The value to set.
     * @param bool $merge Whether to recursively merge or overwrite existing config, defaults to true.
     *
     * @return mixed Config value being read, or the object itself on write operations.
     *
     * @throws \Skinny\Configure\Configure\Exception\Exception When trying to set a key that is invalid.
     */
    public function config($key = null, $value = null, $merge = true)
    {
        if (!$this->configInitialized) {
            $this->config = $this->defaultConfig;
            $this->configInitialized = true;
        }

        if (is_array($key) || func_num_args() >= 2) {
            $this->configWrite($key, $value, $merge);

            return $this;
        }

        return $this->configRead($key);
    }

    /**
     * Merge provided config with existing config. Unlike `config()` which does
     * a recursive merge for nested keys, this method does a simple merge.
     *
     * Setting a specific value:
     *
     * `$this->config('key', $value);`
     *
     * Setting a nested value:
     *
     * `$this->config('some.nested.key', $value);`
     *
     * Updating multiple config settings at the same time:
     *
     * `$this->config(['one' => 'value', 'another' => 'value']);`
     *
     * @param string|array $key The key to set, or a complete array of configs.
     * @param mixed|null $value The value to set.
     * @return $this The object itself.
     */
    public function configShallow($key, $value = null)
    {
        if (!$this->configInitialized) {
            $this->config = $this->defaultConfig;
            $this->configInitialized = true;
        }

        $this->configWrite($key, $value, 'shallow');

        return $this;
    }

    /**
     * Read a config variable.
     *
     * @param string|null $key Key to read.
     * @return mixed
     */
    protected function configRead($key)
    {
        if ($key === null) {
            return $this->config;
        }

        if (strpos($key, '.') === false) {
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }

        $return = $this->config;

        foreach (explode('.', $key) as $k) {
            if (!is_array($return) || !isset($return[$k])) {
                $return = null;
                break;
            }

            $return = $return[$k];
        }

        return $return;
    }

    /**
     * Write a config variable
     *
     * @param string|array $key Key to write to.
     * @param mixed $value Value to write.
     * @param bool|string $merge True to merge recursively, 'shallow' for simple merge,
     *   false to overwrite, defaults to false.
     * @return void
     *
     * @throws \Skinny\Configure\Configure\Exception\Exception if attempting to clobber existing config
     */
    protected function configWrite($key, $value, $merge = false)
    {
        if (is_string($key) && $value === null) {
            $this->configDelete($key);

            return;
        }

        if ($merge) {
            if (is_array($key)) {
                $update = $key;
            } else {
                $update = [$key => $value];
            }
            if ($merge === 'shallow') {
                $this->config = array_merge($this->config, Hash::expand($update));
            } else {
                $this->config = Hash::merge($this->config, Hash::expand($update));
            }

            return;
        }

        if (is_array($key)) {
            foreach ($key as $k => $val) {
                $this->configWrite($k, $val);
            }

            return;
        }

        if (strpos($key, '.') === false) {
            $this->config[$key] = $value;

            return;
        }

        $update =& $this->config;
        $stack = explode('.', $key);

        foreach ($stack as $k) {
            if (!is_array($update)) {
                throw new Exception(sprintf('Cannot set %s value', $key));
            }

            if (!isset($update[$k])) {
                $update[$k] = [];
            }

            $update =& $update[$k];
        }

        $update = $value;
    }

    /**
     * Delete a single config key
     *
     * @param string $key Key to delete.
     * @return void
     *
     * @throws \Skinny\Configure\Configure\Exception\Exception if attempting to clobber existing config
     */
    protected function configDelete($key)
    {
        if (strpos($key, '.') === false) {
            unset($this->config[$key]);

            return;
        }

        $update =& $this->config;
        $stack = explode('.', $key);
        $length = count($stack);

        foreach ($stack as $i => $k) {
            if (!is_array($update)) {
                throw new Exception(sprintf('Cannot unset %s value', $key));
            }

            if (!isset($update[$k])) {
                break;
            }

            if ($i === $length - 2) {
                unset($update[$k]);
                break;
            }

            $update =& $update[$k];
        }
    }
}
