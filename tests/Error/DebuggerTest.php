<?php
namespace SkinnyTest\Core;

use Skinny\TestSuite\TestCase;
use Skinny\Error\Debugger;
use Skinny\Core\Configure;

class DebuggerTest extends TestCase
{
    /**
     * test getInstance.
     *
     * @return void
     */
    public function testGetInstance()
    {
        $result = Debugger::getInstance();
        $this->assertInstanceOf('Skinny\Error\Debugger', $result);

        $result = Debugger::getInstance('SkinnyTest\TestBot\Error\DebuggerTestCase');
        $this->assertInstanceOf('SkinnyTest\TestBot\Error\DebuggerTestCase', $result);

        $result = Debugger::getInstance();
        $this->assertInstanceOf('SkinnyTest\TestBot\Error\DebuggerTestCase', $result);

        $result = Debugger::getInstance('Skinny\Error\Debugger');
        $this->assertInstanceOf('Skinny\Error\Debugger', $result);
    }

    /**
     * testGetType method
     *
     * @return void
     */
    public function testGetType()
    {
        $result = Debugger::getType(new \stdClass);
        $this->assertSame('stdClass', $result);

        $result = Debugger::getType(null);
        $this->assertSame('null', $result);

        $result = Debugger::getType('hi');
        $this->assertSame('string', $result);

        $result = Debugger::getType(['hi']);
        $this->assertSame('array', $result);

        $result = Debugger::getType(123);
        $this->assertSame('integer', $result);

        $result = Debugger::getType(0);
        $this->assertSame('integer', $result);

        $result = Debugger::getType(1);
        $this->assertSame('integer', $result);

        $result = Debugger::getType(true);
        $this->assertSame('boolean', $result);

        $result = Debugger::getType(false);
        $this->assertSame('boolean', $result);

        $result = Debugger::getType(0.5);
        $this->assertSame('float', $result);

        $result = Debugger::getType(10.568);
        $this->assertSame('float', $result);

        $resource = fopen(APP . 'Error' . DS . 'DebuggerTestCase.php', 'r');
        $result = Debugger::getType($resource);
        $this->assertSame('resource', $result);
        fclose($resource);
    }
}
