<?php
namespace SkinnyTest\Message;

use Skinny\TestSuite\TestCase;
use Skinny\Message\Message;

class MessageTest extends TestCase
{
    /**
     * testParse method
     *
     * @return void
     */
    public function testParse()
    {
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
        $this->assertSame($expected, Message::parse('!dev param1 param2 param3'));


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
        $this->assertSame($expected, Message::parse('!dev'));
    }
}
