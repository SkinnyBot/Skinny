<?php
namespace SkinnyTest\Module;

use Skinny\Core\Configure;
use Skinny\Core\Plugin;
use Skinny\Module\ModuleManager;
use Skinny\TestSuite\TestCase;
use SkinnyTest\Lib\Utility;

class ModuleManagerTest extends TestCase
{
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
        $oldName = Utility::callProtectedProperty($ModuleManager, 'loadedModules');

        $result = $ModuleManager->reload('Basic');
        $this->assertSame('L', $result);

        $name = Utility::callProtectedProperty($ModuleManager, 'loadedModules');
        $this->assertNotSame($oldName['Basic']['name'], $name['Basic']['name']);
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
        $result = $ModuleManager->count();
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

        $result = $ModuleManager->offsetGet('Basic');
        $this->assertInstanceOf(
            Utility::callProtectedProperty($ModuleManager, 'loadedModules')['Basic']['name'],
            $result
        );

        $result = $ModuleManager->offsetGet('DoesntExist');
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

        $result = $ModuleManager->offsetExists('Basic');
        $this->assertTrue($result);

        $result = $ModuleManager->offsetExists('DoesntExist');
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

        $result = $ModuleManager->offsetSet('TestModule', $module);
        $this->assertTrue($result);

        $expected = [
            'object' => $module,
            'loaded' => time(),
            'plugin' => false,
            'pluginName' => '',
            'name' => 'SkinnyTest\TestBot\Module\Modules\Test',
            'modified' => false
        ];
        $module = Utility::callProtectedProperty($ModuleManager, 'loadedModules')['TestModule'];
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
        $ModuleManager->offsetSet('TestModule', 'Not an object');
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

        $result = $ModuleManager->offsetSet('PriorityModule', $module);
        $this->assertTrue($result);

        $modules = Utility::callProtectedProperty($ModuleManager, 'loadedModules');
        $expected = [
            0 => 'PriorityModule',
            1 => 'Basic',
            2 => 'Test'
        ];

        $this->assertEquals($expected, array_keys($modules));
    }

    /**
     * testOffsetSet method
     *
     * @return void
     */
    public function testOffsetUnset()
    {
        $ModuleManager = new ModuleManager();
        $this->assertTrue($ModuleManager->offsetUnset('Basic'));
        $this->assertTrue($ModuleManager->offsetUnset('Basic'));
    }
}
