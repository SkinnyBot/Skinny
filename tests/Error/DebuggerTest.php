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

        $file = fopen('php://output', 'w');
        fclose($file);
        $result = Debugger::getType($resource);
        $this->assertSame('unknown', $result);
    }

    /**
     * testExportVar method
     *
     * @return void
     */
    public function testExportVar()
    {
        $result = Debugger::exportVar(true);
        $this->assertSame('true', $result);

        $result = Debugger::exportVar(10.568);
        $this->assertSame('(float) 10.568', $result);

        $result = Debugger::exportVar(10);
        $this->assertSame('(int) 10', $result);

        $result = Debugger::exportVar(null);
        $this->assertSame('null', $result);

        $result = Debugger::exportVar('');
        $this->assertSame("''", $result);

        $result = Debugger::exportVar('skinny');
        $this->assertSame("'skinny'", $result);

        $data = [
            'key' => 'value'
        ];
        $result = Debugger::exportVar($data);
        $expected = <<<TEXT
[
 'key' => 'value'
]
TEXT;
        $this->assertTextEquals($expected, $result);

        $data = [
            'key' => [
                'value'
            ]
        ];
        $result = Debugger::exportVar($data, 1);
        $expected = <<<TEXT
[
 'key' => [
  [maximum depth reached]
 ]
]
TEXT;
        $this->assertTextEquals($expected, $result);

        $data = new \stdClass;
        $data->key = 'value';
        $result = Debugger::exportVar($data);
        $expected = <<<TEXT
object(stdClass) {
 key => 'value'
}
TEXT;
        $this->assertTextEquals($expected, $result);

        $resource = fopen(APP . 'Error' . DS . 'DebuggerTestCase.php', 'r');
        $result = Debugger::exportVar($resource);
        $this->assertSame('resource', $result);
        fclose($resource);

        $file = fopen('php://output', 'w');
        fclose($file);
        $result = Debugger::exportVar($file);
        $this->assertSame('unknown', $result);
    }

    /**
     * testTrace method
     *
     * @return void
     */
    public function testTrace()
    {
        $trace = Debugger::trace(['start' => 1, 'depth' => 2]);
        unset($trace[0]['object']);
        $expected = [
            [
                    'function' => 'testTrace',
                    'class' => 'SkinnyTest\Core\DebuggerTest',
                    'type' => '->',
                    'args' => [],
                    'file' => '[internal]',
                    'line' => '??'
            ]
        ];
        $this->assertSame($expected, $trace);
    }
}
