<?php
namespace SkinnyTest\Utility;

use Skinny\Core\Configure;
use Skinny\TestSuite\TestCase;
use Skinny\Utility\Command;

class CommandTest extends TestCase
{
    /**
     * Setup.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Configure::write([
            'Command' => [
                'prefix' => '!'
            ],
            'Commands' => [
                'say' => [
                    'params' => 1,
                    'syntax' => 'Say [Message]'
                ]
            ]
        ]);

        $this->message = [
            'command' => 'say'
        ];
    }

    /**
     * testSyntax method
     *
     * @return void
     */
    public function testSyntax()
    {
        $expected = 'Not enough parameters given. Syntax: `!Say [Message]`';
        $this->assertSame($expected, Command::syntax($this->message));
    }

    /**
     * testUnknown method
     *
     * @return void
     */
    public function testUnknown()
    {
        $expected = 'Unknown command. Syntax: `Say [Message]`';
        $this->assertSame($expected, Command::unknown($this->message));
    }
}
