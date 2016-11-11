<?php
namespace Skinny\Core\Configure;

use Skinny\Core\Exception\Exception;
use Skinny\Core\Plugin;

/**
 * Trait providing utility methods for file based config engines.
 */
trait FileConfigTrait
{

    /**
     * The path this engine finds files on.
     *
     * @var string
     */
    protected $path = '';

    /**
     * Get file path
     *
     * @param string $key The identifier to write to. If the key has a . it will be treated
     *  as a plugin prefix.
     * @param bool $checkExists Whether to check if file exists. Defaults to false.
     * @return string Full file path
     * @throws \Cake\Core\Exception\Exception When files don't exist or when
     *  files contain '..' as this could lead to abusive reads.
     */
    protected function getFilePath($key, $checkExists = false)
    {
        if (strpos($key, '..') !== false) {
            throw new Exception('Cannot load/dump configuration files with ../ in them.');
        }

        list($plugin, $key) = pluginSplit($key);

        if ($plugin) {
            $file = Plugin::configPath($plugin) . $key;
        } else {
            $file = $this->path . $key;
        }

        $file .= $this->extension;

        if (!$checkExists || is_file($file)) {
            return $file;
        }

        if (is_file(realpath($file))) {
            return realpath($file);
        }

        throw new Exception(sprintf('Could not load configuration file: %s', $file));
    }
}
