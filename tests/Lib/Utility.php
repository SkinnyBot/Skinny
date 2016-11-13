<?php
namespace SkinnyTest\Lib;

class Utility
{

    /**
     * Call a protected method to test it.
     *
     * @param object $obj  The instance of the object.
     * @param string $name The protected method to call.
     * @param array  $args The arguments to pass to the method.
     *
     * @return mixed
     */
    public static function callProtectedMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Call a protected property and return his value.
     *
     * @param object $obj  The instance of the object.
     * @param string $name The protected method to call.
     * @param array  $args The arguments to pass to the method.
     *
     * @return mixed
     */
    public static function callProtectedProperty($obj, $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}
