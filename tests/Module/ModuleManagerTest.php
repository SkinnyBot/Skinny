<?php
namespace SkinnyTest\Module;

use Skinny\Core\Configure;
use Skinny\Core\Plugin;
use Skinny\Module\ModuleManager;
use Skinny\Network\Wrapper;
use Skinny\TestSuite\TestCase;
use SkinnyTest\Lib\Utility;

class ModuleManagerTest extends TestCase
{
    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        Plugin::unload();
        Configure::write('plugins', []);
    }

    /**
     * testPriorityList method
     *
     * @return void
     */
    public function testPriorityList()
    {
        $ModuleManager = new ModuleManager();
        $expected = [
            'Basic',
            'Test'
        ];
        $this->assertSame($expected, $ModuleManager->getLoadedModules());

        $ModuleManager = new ModuleManager(['Test']);
        $expected = [
            'Test',
            'Basic'
        ];
        $this->assertSame($expected, $ModuleManager->getLoadedModules());
    }

    /**
     * testPrefixArgument method
     *
     * @return void
     */
    public function testPrefixArgument()
    {
        $ModuleManager = new ModuleManager();
        $ModuleManager->addPrefixArgument('argument test');
        $this->assertSame('argument test', Utility::callProtectedProperty($ModuleManager, 'prefixArgument'));
    }

    /**
     * testLoadFakeModule method
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ModuleManager::load() expects "FakeModule/Module/Modules/FakeModule_
     *
     * @return void
     */
    public function testLoadFakeModule()
    {
        $ModuleManager = new ModuleManager();
        Plugin::load('FakeModule');
        $result = $ModuleManager->load('FakeModule', ['plugin' => 'FakeModule']);
    }

    /**
     * testLoadNotExistModule method
     *
     * @return void
     */
    public function testLoadNotExistModule()
    {
        $ModuleManager = new ModuleManager();
        $result = $ModuleManager->load('DoesntExist');
        $this->assertSame('NF', $result);
    }

    /**
     * testLoadAlreadyLoaded method
     *
     * @return void
     */
    public function testLoadAlreadyLoaded()
    {
        $ModuleManager = new ModuleManager();
        $result = $ModuleManager->load('Basic');
        $this->assertSame('AL', $result);
    }

    /**
     * testUnload method
     *
     * @return void
     */
    public function testUnload()
    {
        $ModuleManager = new ModuleManager();
        $expected = [
            'Basic',
            'Test'
        ];
        $this->assertSame($expected, $ModuleManager->getLoadedModules());

        $result = $ModuleManager->unload('Basic');
        $this->assertSame('U', $result);
        $this->assertSame(['Test'], $ModuleManager->getLoadedModules());

        $result = $ModuleManager->unload('Basic2');
        $this->assertSame('AU', $result);
        $this->assertSame(['Test'], $ModuleManager->getLoadedModules());
    }

    /**
     * testReload method
     *
     * @return void
     */
    public function testReload()
    {
        $ModuleManager = new ModuleManager();
        $oldName = $ModuleManager['Basic']['name'];

        $result = $ModuleManager->reload('Basic');
        $this->assertSame('L', $result);

        $name = $ModuleManager['Basic']['name'];
        $this->assertNotSame($oldName, $name);
    }

    /**
     * testReloadNotLoaded method
     *
     * @return void
     */
    public function testReloadNotLoaded()
    {
        $ModuleManager = new ModuleManager();

        $result = $ModuleManager->reload('DoesntExist');
        $this->assertSame('AU', $result);
    }

    /**
     * testReloadPlugin method
     *
     * @return void
     */
    public function testReloadPlugin()
    {
        Plugin::load('Developer');
        Configure::write('plugins.Developer', APP);

        $ModuleManager = new ModuleManager();
        $oldName = $ModuleManager['Developer']['name'];

        $result = $ModuleManager->reload('Developer');
        $this->assertSame('L', $result);

        $name = $ModuleManager['Developer']['name'];
        $this->assertNotSame($oldName, $name);
    }

    /**
     * testTimeLoaded method
     *
     * @return void
     */
    public function testTimeLoaded()
    {
        $ModuleManager = new ModuleManager();
        $result = $ModuleManager->timeLoaded('Basic');
        $this->assertSame(time(), $result);
    }

    /**
     * testTimeLoadedNotLoaded method
     *
     * @return void
     */
    public function testTimeLoadedNotLoaded()
    {
        $ModuleManager = new ModuleManager();
        $result = $ModuleManager->timeLoaded('DoesntExist');
        $this->assertFalse($result);
    }

    /**
     * testIsModified method
     *
     * @return void
     */
    public function testIsModified()
    {
        $ModuleManager = new ModuleManager();
        $result = $ModuleManager->isModified('Basic');
        $this->assertTrue($result);

        $result = $ModuleManager->isModified('DoesntExist');
        $this->assertSame(-1, $result);

        Configure::write('debug', false);
        $ModuleManager = new ModuleManager();
        $result = $ModuleManager->isModified('Basic');
        $this->assertFalse($result);
        Configure::write('debug', true);
    }

    /**
     * testCount method
     *
     * @return void
     */
    public function testCount()
    {
        $ModuleManager = new ModuleManager();
        $result = count($ModuleManager);
        $this->assertEquals(2, $result);
    }

    /**
     * testOffsetGet method
     *
     * @return void
     */
    public function testOffsetGet()
    {
        $ModuleManager = new ModuleManager();

        $result = $ModuleManager['Basic']['object'];
        $this->assertInstanceOf(
            $ModuleManager['Basic']['name'],
            $result
        );

        $result = $ModuleManager['DoesntExist'];
        $this->assertFalse($result);
    }

    /**
     * testOffsetExists method
     *
     * @return void
     */
    public function testOffsetExists()
    {
        $ModuleManager = new ModuleManager();

        $result = isset($ModuleManager['Basic']);
        $this->assertTrue($result);

        $result = isset($ModuleManager['DoesntExist']);
        $this->assertFalse($result);
    }

    /**
     * testOffsetSet method
     *
     * @return void
     */
    public function testOffsetSet()
    {
        $ModuleManager = new ModuleManager();
        $ModuleManager->unload('Test');

        $module = new \SkinnyTest\TestBot\Module\Modules\Test();
        $ModuleManager['TestModule']= $module;

        $expected = [
            'object' => $module,
            'loaded' => time(),
            'plugin' => false,
            'pluginName' => '',
            'name' => 'SkinnyTest\TestBot\Module\Modules\Test',
            'modified' => false
        ];
        $module = $ModuleManager['TestModule'];
        unset($module['pluginPath']);

        $this->assertSame($expected, $module);
    }

    /**
     * testOffsetSetNoModuleInstance method
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ModuleManager::offsetSet() expects "Not an object" to be an instance of
     *  ModuleInterface.
     *
     * @return void
     */
    public function testOffsetSetNoModuleInstance()
    {
        $ModuleManager = new ModuleManager();
        $ModuleManager['TestModule'] = 'Not an object';
    }

    /**
     * testOffsetSetPriorityList method
     *
     * @return void
     */
    public function testOffsetSetPriorityList()
    {
        $ModuleManager = new ModuleManager(['PriorityModule']);
        $module = new \SkinnyTest\TestBot\Module\Modules\Test();
        $ModuleManager['PriorityModule']= $module;

        $modules = $ModuleManager->getLoadedModules();
        $expected = [
            0 => 'PriorityModule',
            1 => 'Basic',
            2 => 'Test'
        ];

        $this->assertEquals($expected, $modules);
    }

    /**
     * testOffsetUnset method
     *
     * @return void
     */
    public function testOffsetUnset()
    {
        $ModuleManager = new ModuleManager();
        unset($ModuleManager['Basic']);
        $modules = $ModuleManager->getLoadedModules();

        $this->assertArrayNotHasKey('Basic', $modules);
    }

    /**
     * testCheckPlugins method
     *
     * @return void
     */
    public function testCheckPlugins()
    {
        Plugin::load('Developer');
        Configure::write('plugins.Developer', APP);

        $ModuleManager = new ModuleManager();
        $result = Utility::callProtectedMethod($ModuleManager, 'checkPlugins', ['Developer']);

        $this->assertSame(['pathDir', 'plugin'], array_keys($result));
        $this->assertContains('Skinny/tests/TestBot/plugins/Developer/src/Module/Modules', $result['pathDir']);
    }

    /**
     * testCheckPluginsBadExtension method
     *
     * @return void
     */
    public function testCheckPluginsBadExtension()
    {
        Plugin::load('BadExtension');
        Configure::write('plugins.BadExtension', APP);

        $ModuleManager = new ModuleManager();
        $result = Utility::callProtectedMethod($ModuleManager, 'checkPlugins', ['BadExtension']);
        $this->assertEmpty($result);
    }

    /**
     * testCallMagic method
     *
     * @return void
     */
    public function testCallMagic()
    {
        Plugin::load('CallMagic');
        Configure::write('plugins.CallMagic', APP);

        $ModuleManager = new ModuleManager();
        $Message = $this->createMock('\Discord\Parts\Channel\Message');
        $wrapper = Wrapper::getInstance()->setInstances($Message, $ModuleManager);

        $message = 'magic method onCommandMessage success';

        $result = $ModuleManager->onChannelMessage($wrapper, ['raw' => $message]);
        $this->expectOutputString($message);
    }

    /**
     * testCallMagicWithPriorityAndWithStop method
     *
     * @return void
     */
    public function testCallMagicWithPriorityAndWithStop()
    {
        Plugin::load('CallMagic');
        Configure::write('plugins.CallMagic', APP);

        $ModuleManager = new ModuleManager(['CallMagic']);
        $Message = $this->createMock('\Discord\Parts\Channel\Message');
        $wrapper = Wrapper::getInstance()->setInstances($Message, $ModuleManager);

        $result = $ModuleManager->onPrivateMessage($wrapper, []);
        $this->expectOutputString('testing the return -1');
    }

    /**
     * testCallMagicBadMethod method
     *
     * @return void
     */
    public function testCallMagicBadMethod()
    {
        $ModuleManager = new ModuleManager();
        $Message = $this->createMock('\Discord\Parts\Channel\Message');
        $wrapper = Wrapper::getInstance()->setInstances($Message, $ModuleManager);

        $result = $ModuleManager->thisMethodDoesntExist($wrapper, []);
        $this->assertTrue($result);
    }

    /**
     * testCallMagicWithPrefixArgument method
     *
     * @return void
     */
    public function testCallMagicWithPrefixArgument()
    {
        Plugin::load('CallMagic');
        Configure::write('plugins.CallMagic', APP);

        $ModuleManager = new ModuleManager();
        $ModuleManager->addPrefixArgument('Testing PrefixArgument');
        $Message = $this->createMock('\Discord\Parts\Channel\Message');
        $wrapper = Wrapper::getInstance()->setInstances($Message, $ModuleManager);

        $result = $ModuleManager->onPrefixMessage($wrapper, []);
        $this->expectOutputString('Testing PrefixArgument OK');
    }
}
