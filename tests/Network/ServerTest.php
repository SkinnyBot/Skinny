<?php
namespace SkinnyTest\Network;

use Skinny\Core\Configure;
use Skinny\Network\Server;
use Skinny\TestSuite\TestCase;
use SkinnyTest\Lib\Utility;

class ServerTest extends TestCase
{
    /**
     * testConstructNoToken method
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testConstructNoToken()
    {
        $network = new Server();
    }

    /**
     * testConstruct method
     *
     * @return void
     */
    public function testConstruct()
    {
        Configure::write('Discord.token', 'another token');

        $network = new Server();
        $this->assertInstanceOf('\Discord\Discord', $network->Discord);
        $this->assertInstanceOf('\Skinny\Module\ModuleManager', $network->ModuleManager);
    }

    /**
     * testConstructWithModulesPriority method
     *
     * @return void
     */
    public function testConstructWithModulesPriority()
    {
        Configure::write('Discord.token', 'another token');
        Configure::write('Modules.priority', ['Test']);

        $network = new Server();
        $this->assertSame(['Test'], Utility::callProtectedProperty($network->ModuleManager, 'priorityList'));
    }
}
