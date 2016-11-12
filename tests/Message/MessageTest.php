<?php
namespace SkinnyTest\Message;

use Skinny\TestSuite\TestCase;
use Skinny\Message\Message;

class MessageTest extends TestCase
{
    /**
     * testSetData method
     *
     * @return void
     */
    public function testSetData()
    {
        $Message = Message::getInstance();

        $expected = [
            'raw' => '!dev param1 param2 param3',
            'parts' => [
                    (int) 0 => '!dev',
                    (int) 1 => 'param1 param2 param3'
            ],
            'command' => 'dev',
            'message' => 'param1 param2 param3',
            'commandCode' => '!',
            'arguments' => [
                    (int) 0 => 'param1',
                    (int) 1 => 'param2',
                    (int) 2 => 'param3'
            ]
        ];
        $this->assertSame($expected, $Message::setData('!dev param1 param2 param3'));


        $expected = [
            'raw' => '!dev',
            'parts' => [
                    (int) 0 => '!dev'
            ],
            'command' => 'dev',
            'message' => '',
            'commandCode' => '!',
            'arguments' => []
        ];
        $this->assertSame($expected, $Message::setData('!dev'));
    }

    /**
     * testSetValue method
     *
     * @return void
     */
    public function testSetValue()
    {
        $Message = Message::getInstance();

        $expected = [
            'raw' => '!dev',
            'parts' => [
                    (int) 0 => '!dev'
            ],
            'command' => 'dev',
            'message' => '',
            'commandCode' => '!',
            'arguments' => []
        ];
        $this->assertSame($expected, $Message::setData('!dev'));

        $Message::setValue('raw', '!modified');

        $expected = [
            'raw' => '!modified',
            'parts' => [
                    (int) 0 => '!dev'
            ],
            'command' => 'dev',
            'message' => '',
            'commandCode' => '!',
            'arguments' => []
        ];
        $this->assertSame($expected, $Message::$data);
    }

    /**
     * testSetValue method
     *
     * @return void
     */
    public function testSetValues()
    {
        $Message = Message::getInstance();

        $values = [
            'raw' => '?wtf arg1 arg2 arg3',
            'parts' => [
                '?wtf',
                'arg1 arg2 arg3'
            ],
            'command' => 'wtf',
            'message' => 'arg1 arg2 arg3',
            'commandCode' => '?',
            'arguments' => ['arg1', 'arg2', 'arg3']
        ];

        $Message::setValues($values);
        $this->assertSame($values, $Message::$data);
    }
}
