<?php
namespace SkinnyTest\Utility;

use Skinny\TestSuite\TestCase;
use Skinny\Utility\Text;

class TextTest extends TestCase
{
    /**
     * testTokenize method
     *
     * @return void
     */
    public function testTokenize()
    {
        $result = Text::tokenize('A,(short,boring test)');
        $expected = ['A', '(short,boring test)'];
        $this->assertEquals($expected, $result);

        $result = Text::tokenize('A,(short,more interesting( test)');
        $expected = ['A', '(short,more interesting( test)'];
        $this->assertEquals($expected, $result);

        $result = Text::tokenize('A,(short,very interesting( test))');
        $expected = ['A', '(short,very interesting( test))'];
        $this->assertEquals($expected, $result);

        $result = Text::tokenize('"single tag"', ' ', '"', '"');
        $expected = ['"single tag"'];
        $this->assertEquals($expected, $result);

        $result = Text::tokenize('tagA "single tag" tagB', ' ', '"', '"');
        $expected = ['tagA', '"single tag"', 'tagB'];
        $this->assertEquals($expected, $result);
    }
}
