<?php
namespace SkinnyTest\Utility;

use Skinny\TestSuite\TestCase;
use Skinny\Utility\User;

class UserTest extends TestCase
{

    /**
     * Setup.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->admins = [
            '123456789',
            987654321
        ];
    }

    /**
     * testHasPermission method
     *
     * @return void
     */
    public function testHasPermission()
    {
        $this->assertTrue(User::hasPermission('123456789', $this->admins));
        $this->assertTrue(User::hasPermission(987654321, $this->admins));
        $this->assertTrue(User::hasPermission('987654321', $this->admins));
    }

    /**
     * testHasPermissionFail method
     *
     * @return void
     */
    public function testHasPermissionFail()
    {
        $this->assertFalse(User::hasPermission('12345678', $this->admins));
        $this->assertFalse(User::hasPermission(98765432, $this->admins));
    }
}
