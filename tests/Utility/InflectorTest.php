<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace SkinnyTest\Utility;

use Skinny\TestSuite\TestCase;
use Skinny\Utility\Inflector;

class InflectorTest extends TestCase
{
    /**
     * testCamelize method
     *
     * @return void
     */
    public function testCamelize()
    {
        $this->assertSame('TestThing', Inflector::camelize('test_thing'));
        $this->assertSame('Test-thing', Inflector::camelize('test-thing'));
        $this->assertSame('TestThing', Inflector::camelize('test thing'));

        $this->assertSame('Test_thing', Inflector::camelize('test_thing', '-'));
        $this->assertSame('TestThing', Inflector::camelize('test-thing', '-'));
        $this->assertSame('TestThing', Inflector::camelize('test thing', '-'));

        $this->assertSame('Test_thing', Inflector::camelize('test_thing', ' '));
        $this->assertSame('Test-thing', Inflector::camelize('test-thing', ' '));
        $this->assertSame('TestThing', Inflector::camelize('test thing', ' '));

        $this->assertSame('TestPlugin.TestPluginComments', Inflector::camelize('TestPlugin.TestPluginComments'));
    }

    /**
     * testHumanization method
     *
     * @return void
     */
    public function testHumanization()
    {
        $this->assertEquals('Posts', Inflector::humanize('posts'));
        $this->assertEquals('Posts Tags', Inflector::humanize('posts_tags'));
        $this->assertEquals('File Systems', Inflector::humanize('file_systems'));

        $this->assertEquals('File Systems', Inflector::humanize('file-systems', '-'));
        $this->assertEquals('File Systems', Inflector::humanize('file岡systems', '岡'));
        $this->assertEquals('File Systems Operational', Inflector::humanize('file,systems,operational', ','));

        $this->assertSame('', Inflector::humanize(null));
        $this->assertSame('', Inflector::humanize(false));

        $this->assertSame('Hello Wörld', Inflector::humanize('hello_wörld'));
        $this->assertSame('福岡 City', Inflector::humanize('福岡_city'));
    }
}
