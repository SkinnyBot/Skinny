<?php
namespace SkinnyTest\Message;

use Skinny\TestSuite\TestCase;
use Skinny\Message\Message;
use SkinnyTest\TestBot\Message\StaticMessageTraitTestCase as TestTrait;

class StaticMessageTraitTest extends TestCase
{
    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        TestTrait::resetConfig(false);
    }

    /**
     * testConfigSingleKey method
     *
     * @return void
     */
    public function testConfigSingleKey()
    {
        TestTrait::config('key', 'value');
        $this->assertSame('value', TestTrait::read('key'));

        TestTrait::config('awesome key', 'awesome value');
        $this->assertSame('awesome value', TestTrait::read('awesome key'));
    }

    /**
     * testConfigMultipleKeys method
     *
     * @return void
     */
    public function testConfigMultipleKeys()
    {
        $expected = ['key1' => 'value1', 'key2' => 'value2'];
        TestTrait::config($expected);
        $this->assertSame($expected, TestTrait::read());
    }

    /**
     * testConfigWithObject method
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only string and array can be passed to config.
     *
     * @return void
     */
    public function testConfigWithObject()
    {
        TestTrait::config(new \stdClass);
    }
}
