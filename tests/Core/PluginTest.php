<?php
namespace SkinnyTest\Core;

use Skinny\Core\Configure;
use Skinny\Core\Plugin;
use Skinny\TestSuite\TestCase;
use SkinnyTest\Lib\Utility;

class PluginTest extends TestCase
{
    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload();
        Configure::write('plugins', []);
    }

    /**
     * testLoadSingle method
     *
     * @return void
     */
    public function testLoadSingle()
    {
        Plugin::unload();
        Plugin::load('Developer');
        $expected = ['Developer'];
        $this->assertEquals($expected, Plugin::loaded());
    }

    /**
     * testUnload method
     *
     * @return void
     */
    public function testUnload()
    {
        Plugin::load('Developer');
        $expected = ['Developer'];
        $this->assertEquals($expected, Plugin::loaded());

        Plugin::unload('Developer');
        $this->assertEquals([], Plugin::loaded());

        Plugin::load('Developer');
        $expected = ['Developer'];
        $this->assertEquals($expected, Plugin::loaded());

        Plugin::unload('FakePlugin');
        $this->assertEquals($expected, Plugin::loaded());
    }

    /**
     * testLoadSingleWithBootstrap method
     *
     * @return void
     */
    public function testLoadSingleWithBootstrap()
    {
        Plugin::load('Developer', ['bootstrap' => true]);
        $this->assertTrue(Plugin::loaded('Developer'));
        $this->assertEquals('loaded developer plugin bootstrap', Configure::read('Developer.bootstrap'));
    }

    /**
     * testLoadSingleWithPathConfig method
     *
     * @return void
     */
    public function testLoadSingleWithPathConfig()
    {
        Configure::write('plugins.Developer', APP);
        Plugin::load('Developer');
        $this->assertEquals(APP . 'src' . DS, Plugin::classPath('Developer'));
    }

    /**
     * testLoadMultiple method
     *
     * @return void
     */
    public function testLoadMultiple()
    {
        Plugin::load(['CallMagic', 'Developer']);
        $expected = ['CallMagic', 'Developer'];
        $this->assertEquals($expected, Plugin::loaded());
    }

    /**
     * testLoadMultipleWithDefaults method
     *
     * @return void
     */
    public function testLoadMultipleWithDefaults()
    {
        Plugin::load(['CallMagic', 'Developer'], ['bootstrap' => true, 'routes' => false]);
        $expected = ['CallMagic', 'Developer'];

        $this->assertEquals($expected, Plugin::loaded());
        $this->assertEquals('loaded developer plugin bootstrap', Configure::read('Developer.bootstrap'));
        $this->assertEquals('loaded callmagic plugin bootstrap', Configure::read('CallMagic.bootstrap'));
    }

    /**
     * testIgnoreMissingFiles method
     *
     * @return void
     */
    public function testIgnoreMissingFiles()
    {
        Plugin::loadAll([[
                'bootstrap' => true,
                'ignoreMissing' => true
        ]]);

        $expected = [
            0 => 'BadExtension',
            1 => 'CallMagic',
            2 => 'Developer',
            3 => 'FakeModule'
        ];
        $this->assertSame($expected, Plugin::loaded());
    }

    /**
     * testLoadNotFound method
     *
     * @expectedException \Skinny\Core\Exception\MissingPluginException
     *
     * @return void
     */
    public function testLoadNotFound()
    {
        Plugin::load('MissingPlugin');
    }

    /**
     * testPath method
     *
     * @return void
     */
    public function testPath()
    {
        Plugin::load(['Developer', 'CallMagic']);
        $expected = APP . 'plugins' . DS . 'Developer' . DS;
        $this->assertSame(Plugin::path('Developer'), $expected);

        $expected = APP . 'plugins' . DS . 'CallMagic' . DS;
        $this->assertSame(Plugin::path('CallMagic'), $expected);
    }

    /**
     * testPathNotFound method
     *
     * @expectedException \Skinny\Core\Exception\MissingPluginException
     *
     * @return void
     */
    public function testPathNotFound()
    {
        Plugin::path('Developer');
    }

    /**
     * testClassPath method
     *
     * @return void
     */
    public function testClassPath()
    {
        Plugin::load(['Developer', 'CallMagic']);
        $expected = APP . 'plugins' . DS . 'Developer' . DS . 'src' . DS;
        $this->assertSame(Plugin::classPath('Developer'), $expected);

        $expected = APP . 'plugins' . DS . 'CallMagic' . DS . 'src' . DS;
        $this->assertSame(Plugin::classPath('CallMagic'), $expected);
    }

    /**
     * testClassPathNotFound method
     *
     * @expectedException \Skinny\Core\Exception\MissingPluginException
     *
     * @return void
     */
    public function testClassPathNotFound()
    {
        Plugin::classPath('Developer');
    }

    /**
     * testLoadAll method
     *
     * @return void
     */
    public function testLoadAll()
    {
        Plugin::loadAll();
        $expected = ['BadExtension', 'CallMagic', 'Developer', 'FakeModule'];
        $this->assertEquals($expected, Plugin::loaded());
    }

    /**
     * testLoadAllWithPathConfig method
     *
     * @return void
     */
    public function testLoadAllWithPathConfig()
    {
        Configure::write('plugins.FakePlugin', APP);
        Plugin::loadAll();
        $this->assertContains('FakePlugin', Plugin::loaded());
    }

    /**
     * testConfigPath method
     *
     * @return void
     */
    public function testConfigPath()
    {
        Plugin::load(['Developer', 'CallMagic']);
        $expected = APP . 'plugins' . DS . 'Developer' . DS . 'config' . DS;
        $this->assertSame(Plugin::configPath('Developer'), $expected);

        $expected = APP . 'plugins' . DS . 'CallMagic' . DS . 'config' . DS;
        $this->assertSame(Plugin::configPath('CallMagic'), $expected);
    }

    /**
     * testConfigPathNotFound method
     *
     * @expectedException \Skinny\Core\Exception\MissingPluginException
     *
     * @return void
     */
    public function testConfigPathNotFound()
    {
        Plugin::configPath('Developer');
    }

    /**
     * testLoadedWithValuesOnePlugin method
     *
     * @return void
     */
    public function testLoadedWithValuesOnePlugin()
    {
        Plugin::load('Developer');
        $result = Plugin::loadedWithValues('Developer');
        $expected = [
            'bootstrap',
            'classBase',
            'ignoreMissing',
            'path',
            'classPath',
            'configPath'
        ];
        $this->assertEquals($expected, array_keys($result));
    }

    /**
     * testLoadedWithValues method
     *
     * @return void
     */
    public function testLoadedWithValues()
    {
        Plugin::load(['Developer', 'CallMagic']);
        $result = Plugin::loadedWithValues();
        $expected = [
            'CallMagic' => [
                'bootstrap',
                'classBase',
                'ignoreMissing',
                'path',
                'classPath',
                'configPath'
            ],
            'Developer' => [
                'bootstrap',
                'classBase',
                'ignoreMissing',
                'path',
                'classPath',
                'configPath'
            ]
        ];
        $this->assertEquals($expected, $this->getL2Keys($result));
    }

    /**
     * Get the first en second keys level of an array.
     *
     * @param array $array The array to match
     *
     * @return array
     */
    protected function getL2Keys($array)
    {
        $result = [];

        foreach ($array as $key => $values) {
            $result[$key] = array_keys($values);
        }

        return $result;
    }
}
