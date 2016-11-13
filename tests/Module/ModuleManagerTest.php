<?php
namespace SkinnyTest\Module;

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
}
