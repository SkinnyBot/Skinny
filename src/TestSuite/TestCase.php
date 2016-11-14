<?php
namespace Skinny\TestSuite;

use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Assert text equality, ignoring differences in newlines.
     * Helpful for doing cross platform tests of blocks of text.
     *
     * @param string $expected The expected value.
     * @param string $result The actual value.
     * @param string $message The message to use for failure.
     * @return void
     */
    public function assertTextEquals($expected, $result, $message = '')
    {
        $expected = str_replace(["\r\n", "\r"], "\n", $expected);
        $result = str_replace(["\r\n", "\r"], "\n", $result);
        $expected = str_replace("\t", " ", $expected);
        $result = str_replace("\t", " ", $result);
        $this->assertEquals($expected, $result, $message);
    }
}
