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
use Skinny\Utility\Hash;

class HashTest extends TestCase
{
    /**
     * Data provider
     *
     * @return array
     */
    public static function articleData()
    {
        return [
            [
                'Article' => [
                    'id' => '1',
                    'user_id' => '1',
                    'title' => 'First Article',
                    'body' => 'First Article Body'
                ],
                'User' => [
                    'id' => '1',
                    'user' => 'mariano',
                    'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
                ],
                'Comment' => [
                    [
                        'id' => '1',
                        'article_id' => '1',
                        'user_id' => '2',
                        'comment' => 'First Comment for First Article',
                    ],
                    [
                        'id' => '2',
                        'article_id' => '1',
                        'user_id' => '4',
                        'comment' => 'Second Comment for First Article',
                    ],
                ],
                'Tag' => [
                    [
                        'id' => '1',
                        'tag' => 'tag1',
                    ],
                    [
                        'id' => '2',
                        'tag' => 'tag2',
                    ]
                ],
                'Deep' => [
                    'Nesting' => [
                        'test' => [
                            1 => 'foo',
                            2 => [
                                'and' => ['more' => 'stuff']
                            ]
                        ]
                    ]
                ]
            ],
            [
                'Article' => [
                    'id' => '2',
                    'user_id' => '1',
                    'title' => 'Second Article',
                    'body' => 'Second Article Body',
                    'published' => 'Y',
                ],
                'User' => [
                    'id' => '2',
                    'user' => 'mariano',
                    'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
                ],
                'Comment' => [],
                'Tag' => []
            ],
            [
                'Article' => [
                    'id' => '3',
                    'user_id' => '1',
                    'title' => 'Third Article',
                    'body' => 'Third Article Body',
                ],
                'User' => [
                    'id' => '3',
                    'user' => 'mariano',
                    'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
                ],
                'Comment' => [],
                'Tag' => []
            ],
            [
                'Article' => [
                    'id' => '4',
                    'user_id' => '1',
                    'title' => 'Fourth Article',
                    'body' => 'Fourth Article Body',
                ],
                'User' => [
                    'id' => '4',
                    'user' => 'mariano',
                    'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
                ],
                'Comment' => [],
                'Tag' => []
            ],
            [
                'Article' => [
                    'id' => '5',
                    'user_id' => '1',
                    'title' => 'Fifth Article',
                    'body' => 'Fifth Article Body',
                ],
                'User' => [
                    'id' => '5',
                    'user' => 'mariano',
                    'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
                    ],
                'Comment' => [],
                'Tag' => []
            ]
        ];
    }

    /**
     * Test insert()
     *
     * @return void
     */
    public function testInsertSimple()
    {
        $a = [
            'pages' => ['name' => 'page']
        ];
        $result = Hash::insert($a, 'files', ['name' => 'files']);
        $expected = [
            'pages' => ['name' => 'page'],
            'files' => ['name' => 'files']
        ];
        $this->assertEquals($expected, $result);

        $a = [
            'pages' => ['name' => 'page']
        ];
        $result = Hash::insert($a, 'pages.name', []);
        $expected = [
            'pages' => ['name' => []],
        ];
        $this->assertEquals($expected, $result);

        $a = [
            'foo' => ['bar' => 'baz']
        ];
        $result = Hash::insert($a, 'some.0123.path', ['foo' => ['bar' => 'baz']]);
        $expected = ['foo' => ['bar' => 'baz']];
        $this->assertEquals($expected, Hash::get($result, 'some.0123.path'));
    }

    /**
     * Test inserting with multiple values.
     *
     * @return void
     */
    public function testInsertMulti()
    {
        $data = static::articleData();

        $result = Hash::insert($data, '{n}.Article.insert', 'value');
        $this->assertEquals('value', $result[0]['Article']['insert']);
        $this->assertEquals('value', $result[1]['Article']['insert']);

        $result = Hash::insert($data, '{n}.Comment.{n}.insert', 'value');
        $this->assertEquals('value', $result[0]['Comment'][0]['insert']);
        $this->assertEquals('value', $result[0]['Comment'][1]['insert']);

        $data = [
            0 => ['Item' => ['id' => 1, 'title' => 'first']],
            1 => ['Item' => ['id' => 2, 'title' => 'second']],
            2 => ['Item' => ['id' => 3, 'title' => 'third']],
            3 => ['Item' => ['id' => 4, 'title' => 'fourth']],
            4 => ['Item' => ['id' => 5, 'title' => 'fifth']],
        ];
        $result = Hash::insert($data, '{n}.Item[id=/\b2|\b4/]', ['test' => 2]);
        $expected = [
            0 => ['Item' => ['id' => 1, 'title' => 'first']],
            1 => ['Item' => ['id' => 2, 'title' => 'second', 'test' => 2]],
            2 => ['Item' => ['id' => 3, 'title' => 'third']],
            3 => ['Item' => ['id' => 4, 'title' => 'fourth', 'test' => 2]],
            4 => ['Item' => ['id' => 5, 'title' => 'fifth']],
        ];
        $this->assertEquals($expected, $result);

        $data[3]['testable'] = true;
        $result = Hash::insert($data, '{n}[testable].Item[id=/\b2|\b4/].test', 2);
        $expected = [
            0 => ['Item' => ['id' => 1, 'title' => 'first']],
            1 => ['Item' => ['id' => 2, 'title' => 'second']],
            2 => ['Item' => ['id' => 3, 'title' => 'third']],
            3 => ['Item' => ['id' => 4, 'title' => 'fourth', 'test' => 2], 'testable' => true],
            4 => ['Item' => ['id' => 5, 'title' => 'fifth']],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test get()
     *
     * @return void
     */
    public function testGet()
    {
        $data = ['abc', 'def'];

        $result = Hash::get($data, '0');
        $this->assertEquals('abc', $result);

        $result = Hash::get($data, 0);
        $this->assertEquals('abc', $result);

        $result = Hash::get($data, '1');
        $this->assertEquals('def', $result);

        $data = self::articleData();

        $result = Hash::get([], '1.Article.title');
        $this->assertNull($result);

        $result = Hash::get($data, '');
        $this->assertNull($result);

        $result = Hash::get($data, null, '-');
        $this->assertSame('-', $result);

        $result = Hash::get($data, '0.Article.title');
        $this->assertEquals('First Article', $result);

        $result = Hash::get($data, '1.Article.title');
        $this->assertEquals('Second Article', $result);

        $result = Hash::get($data, '5.Article.title');
        $this->assertNull($result);

        $default = ['empty'];
        $this->assertEquals($default, Hash::get($data, '5.Article.title', $default));
        $this->assertEquals($default, Hash::get([], '5.Article.title', $default));

        $result = Hash::get($data, '1.Article.title.not_there');
        $this->assertNull($result);

        $result = Hash::get($data, '1.Article');
        $this->assertEquals($data[1]['Article'], $result);

        $result = Hash::get($data, ['1', 'Article']);
        $this->assertEquals($data[1]['Article'], $result);
    }

    /**
     * Test that get() can extract '' key data.
     *
     * @return void
     */
    public function testGetEmptyKey()
    {
        $data = [
            '' => 'some value'
        ];
        $result = Hash::get($data, '');
        $this->assertSame($data[''], $result);
    }

    /**
     * Test get() for invalid $data type
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid data type, must be an array or \ArrayAccess instance.
     *
     * @return void
     */
    public function testGetInvalidData()
    {
        Hash::get('string', 'path');
    }

    /**
     * Test get() with an invalid path
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testGetInvalidPath()
    {
        Hash::get(['one' => 'two'], true);
    }

    /**
     * Test remove() method.
     *
     * @return void
     */
    public function testRemove()
    {
        $a = [
            'pages' => ['name' => 'page'],
            'files' => ['name' => 'files']
        ];

        $result = Hash::remove($a, 'files');
        $expected = [
            'pages' => ['name' => 'page']
        ];
        $this->assertEquals($expected, $result);

        $a = [
            'pages' => [
                0 => ['name' => 'main'],
                1 => [
                    'name' => 'about',
                    'vars' => ['title' => 'page title']
                ]
            ]
        ];

        $result = Hash::remove($a, 'pages.1.vars');
        $expected = [
            'pages' => [
                0 => ['name' => 'main'],
                1 => ['name' => 'about']
            ]
        ];
        $this->assertEquals($expected, $result);

        $result = Hash::remove($a, 'pages.2.vars');
        $expected = $a;
        $this->assertEquals($expected, $result);

        $a = [
            0 => [
                'name' => 'pages'
            ],
            1 => [
                'name' => 'files'
            ]
        ];

        $result = Hash::remove($a, '{n}[name=files]');
        $expected = [
            0 => [
                'name' => 'pages'
            ]
        ];
        $this->assertEquals($expected, $result);

        $array = [
            0 => 'foo',
            1 => [
                0 => 'baz'
            ]
        ];
        $expected = $array;
        $result = Hash::remove($array, '{n}.part');
        $this->assertEquals($expected, $result);
        $result = Hash::remove($array, '{n}.{n}.part');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test removing multiple values.
     *
     * @return void
     */
    public function testRemoveMulti()
    {
        $data = static::articleData();

        $result = Hash::remove($data, '{n}.Article.title');
        $this->assertFalse(isset($result[0]['Article']['title']));
        $this->assertFalse(isset($result[1]['Article']['title']));

        $result = Hash::remove($data, '{n}.Article.{s}');
        $this->assertFalse(isset($result[0]['Article']['id']));
        $this->assertFalse(isset($result[0]['Article']['user_id']));
        $this->assertFalse(isset($result[0]['Article']['title']));
        $this->assertFalse(isset($result[0]['Article']['body']));

        $data = [
            0 => ['Item' => ['id' => 1, 'title' => 'first']],
            1 => ['Item' => ['id' => 2, 'title' => 'second']],
            2 => ['Item' => ['id' => 3, 'title' => 'third']],
            3 => ['Item' => ['id' => 4, 'title' => 'fourth']],
            4 => ['Item' => ['id' => 5, 'title' => 'fifth']],
        ];

        $result = Hash::remove($data, '{n}.Item[id=/\b2|\b4/]');
        $expected = [
            0 => ['Item' => ['id' => 1, 'title' => 'first']],
            2 => ['Item' => ['id' => 3, 'title' => 'third']],
            4 => ['Item' => ['id' => 5, 'title' => 'fifth']],
        ];
        $this->assertEquals($expected, $result);

        $data[3]['testable'] = true;
        $result = Hash::remove($data, '{n}[testable].Item[id=/\b2|\b4/].title');
        $expected = [
            0 => ['Item' => ['id' => 1, 'title' => 'first']],
            1 => ['Item' => ['id' => 2, 'title' => 'second']],
            2 => ['Item' => ['id' => 3, 'title' => 'third']],
            3 => ['Item' => ['id' => 4], 'testable' => true],
            4 => ['Item' => ['id' => 5, 'title' => 'fifth']],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests Hash::expand
     *
     * @return void
     */
    public function testExpand()
    {
        $data = ['My', 'Array', 'To', 'Flatten'];
        $flat = Hash::flatten($data);
        $result = Hash::expand($flat);
        $this->assertEquals($data, $result);

        $data = [
            '0.Post.id' => '1', '0.Post.author_id' => '1', '0.Post.title' => 'First Post', '0.Author.id' => '1',
            '0.Author.user' => 'nate', '0.Author.password' => 'foo', '1.Post.id' => '2', '1.Post.author_id' => '3',
            '1.Post.title' => 'Second Post', '1.Post.body' => 'Second Post Body', '1.Author.id' => '3',
            '1.Author.user' => 'larry', '1.Author.password' => null
        ];
        $result = Hash::expand($data);
        $expected = [
            [
                'Post' => ['id' => '1', 'author_id' => '1', 'title' => 'First Post'],
                'Author' => ['id' => '1', 'user' => 'nate', 'password' => 'foo'],
            ],
            [
                'Post' => ['id' => '2', 'author_id' => '3', 'title' => 'Second Post', 'body' => 'Second Post Body'],
                'Author' => ['id' => '3', 'user' => 'larry', 'password' => null],
            ]
        ];
        $this->assertEquals($expected, $result);

        $data = [
            '0/Post/id' => 1,
            '0/Post/name' => 'test post'
        ];
        $result = Hash::expand($data, '/');
        $expected = [
            [
                'Post' => [
                    'id' => 1,
                    'name' => 'test post'
                ]
            ]
        ];
        $this->assertEquals($expected, $result);

        $data = ['a.b.100.a' => null, 'a.b.200.a' => null];
        $expected = [
            'a' => [
                'b' => [
                    100 => ['a' => null],
                    200 => ['a' => null]
                ]
            ]
        ];
        $result = Hash::expand($data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests Hash::flatten
     *
     * @return void
     */
    public function testFlatten()
    {
        $data = ['Larry', 'Curly', 'Moe'];
        $result = Hash::flatten($data);
        $this->assertEquals($result, $data);

        $data[9] = 'Shemp';
        $result = Hash::flatten($data);
        $this->assertEquals($result, $data);

        $data = [
            [
                'Post' => ['id' => '1', 'author_id' => '1', 'title' => 'First Post'],
                'Author' => ['id' => '1', 'user' => 'nate', 'password' => 'foo'],
            ],
            [
                'Post' => ['id' => '2', 'author_id' => '3', 'title' => 'Second Post', 'body' => 'Second Post Body'],
                'Author' => ['id' => '3', 'user' => 'larry', 'password' => null],
            ]
        ];
        $result = Hash::flatten($data);
        $expected = [
            '0.Post.id' => '1',
            '0.Post.author_id' => '1',
            '0.Post.title' => 'First Post',
            '0.Author.id' => '1',
            '0.Author.user' => 'nate',
            '0.Author.password' => 'foo',
            '1.Post.id' => '2',
            '1.Post.author_id' => '3',
            '1.Post.title' => 'Second Post',
            '1.Post.body' => 'Second Post Body',
            '1.Author.id' => '3',
            '1.Author.user' => 'larry',
            '1.Author.password' => null
        ];
        $this->assertEquals($expected, $result);

        $data = [
            [
                'Post' => ['id' => '1', 'author_id' => null, 'title' => 'First Post'],
                'Author' => [],
            ]
        ];
        $result = Hash::flatten($data);
        $expected = [
            '0.Post.id' => '1',
            '0.Post.author_id' => null,
            '0.Post.title' => 'First Post',
            '0.Author' => []
        ];
        $this->assertEquals($expected, $result);

        $data = [
            ['Post' => ['id' => 1]],
            ['Post' => ['id' => 2]],
        ];
        $result = Hash::flatten($data, '/');
        $expected = [
            '0/Post/id' => '1',
            '1/Post/id' => '2',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merge()
     *
     * @return void
     */
    public function testMerge()
    {
        $result = Hash::merge(['foo'], ['bar']);
        $this->assertEquals($result, ['foo', 'bar']);

        $a = ['foo', 'foo2'];
        $b = ['bar', 'bar2'];
        $expected = ['foo', 'foo2', 'bar', 'bar2'];
        $this->assertEquals($expected, Hash::merge($a, $b));

        $a = ['foo' => 'bar', 'bar' => 'foo'];
        $b = ['foo' => 'no-bar', 'bar' => 'no-foo'];
        $expected = ['foo' => 'no-bar', 'bar' => 'no-foo'];
        $this->assertEquals($expected, Hash::merge($a, $b));

        $a = ['users' => ['bob', 'jim']];
        $b = ['users' => ['lisa', 'tina']];
        $expected = ['users' => ['bob', 'jim', 'lisa', 'tina']];
        $this->assertEquals($expected, Hash::merge($a, $b));

        $a = ['users' => ['jim', 'bob']];
        $b = ['users' => 'none'];
        $expected = ['users' => 'none'];
        $this->assertEquals($expected, Hash::merge($a, $b));

        $a = [
            'Tree',
            'CounterCache',
            'Upload' => [
                'folder' => 'products',
                'fields' => ['image_1_id', 'image_2_id', 'image_3_id', 'image_4_id', 'image_5_id']
            ]
        ];
        $b = [
            'Cacheable' => ['enabled' => false],
            'Limit',
            'Bindable',
            'Validator',
            'Transactional'
        ];
        $expected = [
            'Tree',
            'CounterCache',
            'Upload' => [
                'folder' => 'products',
                'fields' => ['image_1_id', 'image_2_id', 'image_3_id', 'image_4_id', 'image_5_id']
            ],
            'Cacheable' => ['enabled' => false],
            'Limit',
            'Bindable',
            'Validator',
            'Transactional'
        ];
        $this->assertEquals($expected, Hash::merge($a, $b));
    }
}
