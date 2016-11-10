<?php
namespace Skinny\Singleton;

use Exception;

class Singleton
{
    /**
     * All classes instances.
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Set the constructor in protected because the class must not be instantiated by it.
     */
    protected function __construct()
    {
    }

    /**
     * Same as the constructor.
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * Same as the constructor.
     *
     * @return void
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get the intance of the class.
     *
     * @return object
     */
    public static function getInstance()
    {
        $cls = get_called_class();
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }

        return self::$instances[$cls];
    }
}
