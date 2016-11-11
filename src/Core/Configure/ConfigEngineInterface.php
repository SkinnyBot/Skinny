<?php
namespace Skinny\Core\Configure;

interface ConfigEngineInterface
{

    /**
     * Read method is used for reading configuration information from sources.
     * These sources can either be static resources like files, or dynamic ones like
     * a database, or other datasource.
     *
     * @param string $key Key to read.
     *
     * @return array An array of data to merge into the runtime configuration.
     */
    public function read($key);

    /**
     * Dumps the configure data into source.
     *
     * @param string $key The identifier to write to.
     * @param array $data The data to dump.
     *
     * @return bool True on success or false on failure.
     */
    public function dump($key, array $data);
}
