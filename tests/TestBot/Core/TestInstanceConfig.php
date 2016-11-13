<?php
namespace SkinnyTest\TestBot\Core;

use Skinny\Core\InstanceConfigTrait;

class TestInstanceConfig
{
    use InstanceConfigTrait;

    /**
     * defaultConfig
     *
     * Some default config
     *
     * @var array
     */
    protected $defaultConfig = [
        'some' => 'string',
        'a' => [
            'nested' => 'value',
            'other' => 'value'
        ]
    ];
}
