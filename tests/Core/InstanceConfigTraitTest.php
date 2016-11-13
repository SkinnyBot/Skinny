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
namespace SkinnyTest\Core;

use Skinny\TestSuite\TestCase;
use SkinnyTest\TestBot\Core\TestInstanceConfig;

class InstanceConfigTraitTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->object = new TestInstanceConfig();
    }

    /**
     * testDefaultsAreSet method
     *
     * @return void
     */
    public function testDefaultsAreSet()
    {
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nested' => 'value',
                    'other' => 'value'
                ]
            ],
            $this->object->config(),
            'runtime config should match the defaults if not overridden'
        );
    }

    /**
     * testGetSimple method
     *
     * @return void
     */
    public function testGetSimple()
    {
        $this->assertSame(
            'string',
            $this->object->config('some'),
            'should return the key value only'
        );
        $this->assertSame(
            ['nested' => 'value', 'other' => 'value'],
            $this->object->config('a'),
            'should return the key value only'
        );
    }

    /**
     * testGetDot method
     *
     * @return void
     */
    public function testGetDot()
    {
        $this->assertSame(
            'value',
            $this->object->config('a.nested'),
            'should return the nested value only'
        );
    }

    /**
     * testSetSimple method
     *
     * @return void
     */
    public function testSetSimple()
    {
        $this->object->config('foo', 'bar');
        $this->assertSame(
            'bar',
            $this->object->config('foo'),
            'should return the same value just set'
        );
        $return = $this->object->config('some', 'zum');
        $this->assertSame(
            'zum',
            $this->object->config('some'),
            'should return the overritten value'
        );
        $this->assertSame(
            $this->object,
            $return,
            'write operations should return the instance'
        );
        $this->assertSame(
            [
                'some' => 'zum',
                'a' => ['nested' => 'value', 'other' => 'value'],
                'foo' => 'bar',
            ],
            $this->object->config(),
            'updates should be merged with existing config'
        );
    }

    /**
     * testSetNested method
     *
     * @return void
     */
    public function testSetNested()
    {
        $this->object->config('new.foo', 'bar');
        $this->assertSame(
            'bar',
            $this->object->config('new.foo'),
            'should return the same value just set'
        );
        $this->object->config('a.nested', 'zum');
        $this->assertSame(
            'zum',
            $this->object->config('a.nested'),
            'should return the overritten value'
        );
        $this->assertSame(
            [
                'some' => 'string',
                'a' => ['nested' => 'zum', 'other' => 'value'],
                'new' => ['foo' => 'bar']
            ],
            $this->object->config(),
            'updates should be merged with existing config'
        );
    }

    /**
     * testSetNested method
     *
     * @return void
     */
    public function testSetArray()
    {
        $this->object->config(['foo' => 'bar']);
        $this->assertSame(
            'bar',
            $this->object->config('foo'),
            'should return the same value just set'
        );
        $this->assertSame(
            [
                'some' => 'string',
                'a' => ['nested' => 'value', 'other' => 'value'],
                'foo' => 'bar',
            ],
            $this->object->config(),
            'updates should be merged with existing config'
        );
        $this->object->config(['new.foo' => 'bar']);
        $this->assertSame(
            'bar',
            $this->object->config('new.foo'),
            'should return the same value just set'
        );
        $this->assertSame(
            [
                'some' => 'string',
                'a' => ['nested' => 'value', 'other' => 'value'],
                'foo' => 'bar',
                'new' => ['foo' => 'bar']
            ],
            $this->object->config(),
            'updates should be merged with existing config'
        );
        $this->object->config(['multiple' => 'different', 'a.values.to' => 'set']);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => ['nested' => 'value', 'other' => 'value', 'values' => ['to' => 'set']],
                'foo' => 'bar',
                'new' => ['foo' => 'bar'],
                'multiple' => 'different'
            ],
            $this->object->config(),
            'updates should be merged with existing config'
        );
    }

    /**
     * testConfigShallow method
     *
     * @return void
     */
    public function testConfigShallow()
    {
        $this->object->configShallow(['a' => ['new_nested' => true], 'new' => 'bar']);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => ['new_nested' => true],
                'new' => 'bar'
            ],
            $this->object->config(),
            'When merging a scalar property will be overwritten with an array'
        );
    }

    /**
     * testSetClobber method
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot set a.nested.value
     * @return void
     */
    public function testSetClobber()
    {
        $this->object->config(['a.nested.value' => 'not possible'], null, false);
        $result = $this->object->config();
    }

    /**
     * testMerge method
     *
     * @return void
     */
    public function testMerge()
    {
        $this->object->config(['a' => ['nother' => 'value']]);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nested' => 'value',
                    'other' => 'value',
                    'nother' => 'value'
                ]
            ],
            $this->object->config(),
            'Merging should not delete untouched array values'
        );
    }

    /**
     * testMergeDotKey method
     *
     * @return void
     */
    public function testMergeDotKey()
    {
        $this->object->config('a.nother', 'value');
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nested' => 'value',
                    'other' => 'value',
                    'nother' => 'value'
                ]
            ],
            $this->object->config(),
            'Should act the same as having passed the equivalent array to the config function'
        );
        $this->object->config(['a.nextra' => 'value']);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nested' => 'value',
                    'other' => 'value',
                    'nother' => 'value',
                    'nextra' => 'value'
                ]
            ],
            $this->object->config(),
            'Merging should not delete untouched array values'
        );
    }

    /**
     * testSetDefaultsMerge method
     *
     * @return void
     */
    public function testSetDefaultsMerge()
    {
        $this->object->config(['a' => ['nother' => 'value']]);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nested' => 'value',
                    'other' => 'value',
                    'nother' => 'value'
                ]
            ],
            $this->object->config(),
            'First access should act like any subsequent access'
        );
    }

    /**
     * testSetDefaultsNoMerge method
     *
     * @return void
     */
    public function testSetDefaultsNoMerge()
    {
        $this->object->config(['a' => ['nother' => 'value']], null, false);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nother' => 'value'
                ]
            ],
            $this->object->config(),
            'If explicitly no-merge, array values should be overwritten'
        );
    }

    /**
     * testSetMergeNoClobber method
     *
     * Merging offers no such protection of clobbering a value whilst implemented
     * using the Hash class
     *
     * @return void
     */
    public function testSetMergeNoClobber()
    {
        $this->object->config(['a.nested.value' => 'it is possible']);
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'nested' => [
                        'value' => 'it is possible'
                    ],
                    'other' => 'value'
                ]
            ],
            $this->object->config(),
            'When merging a scalar property will be overwritten with an array'
        );
    }

    /**
     * testDeleteSimple method
     *
     * @return void
     */
    public function testDeleteSimple()
    {
        $this->object->config('foo', null);
        $this->assertNull(
            $this->object->config('foo'),
            'setting a new key to null should have no effect'
        );
        $this->object->config('some', null);
        $this->assertNull(
            $this->object->config('some'),
            'should delete the existing value'
        );
        $this->assertSame(
            [
                'a' => ['nested' => 'value', 'other' => 'value'],
            ],
            $this->object->config(),
            'deleted keys should not be present'
        );
    }

    /**
     * testDeleteNested method
     *
     * @return void
     */
    public function testDeleteNested()
    {
        $this->object->config('new.foo', null);
        $this->assertNull(
            $this->object->config('new.foo'),
            'setting a new key to null should have no effect'
        );
        $this->object->config('a.nested', null);
        $this->assertNull(
            $this->object->config('a.nested'),
            'should delete the existing value'
        );
        $this->assertSame(
            [
                'some' => 'string',
                'a' => [
                    'other' => 'value'
                ]
            ],
            $this->object->config(),
            'deleted keys should not be present'
        );
        $this->object->config('a.other', null);
        $this->assertNull(
            $this->object->config('a.other'),
            'should delete the existing value'
        );
        $this->assertSame(
            [
                'some' => 'string',
                'a' => []
            ],
            $this->object->config(),
            'deleted keys should not be present'
        );
    }

    /**
     * testDeleteArray method
     *
     * @return void
     */
    public function testDeleteArray()
    {
        $this->object->config('a', null);
        $this->assertNull(
            $this->object->config('a'),
            'should delete the existing value'
        );
        $this->assertSame(
            [
                'some' => 'string'
            ],
            $this->object->config(),
            'deleted keys should not be present'
        );
    }

    /**
     * testDeleteClobber method
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot unset a.nested.value.whoops
     * @return void
     */
    public function testDeleteClobber()
    {
        $this->object->config('a.nested.value.whoops', null);
    }
}
