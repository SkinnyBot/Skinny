# Skinny Bot

<p align="center">
  <img src="https://cloud.githubusercontent.com/assets/8210023/20224403/9d9212b2-a83e-11e6-8672-e43b513c480c.jpg" alt="Skinny Logo" height="120"/>
</p>

|Travis|Coverage|Codacy|StyleCI|Stable Version|Downloads|License|
|:------:|:-------:|:-------:|:-------:|:-------:|:------:|:------:|
|[![Build Status](https://img.shields.io/travis/SkinnyBot/Skinny.svg?style=flat-square)](https://travis-ci.org/SkinnyBot/Skinny)|[![Coverage](https://img.shields.io/codacy/coverage/9c1cf2a17fd04241a0292a2844bb5de2.svg?style=flat-square)](https://www.codacy.com/app/SkinnyBot/Skinny)|[![Codacy](https://img.shields.io/codacy/grade/9c1cf2a17fd04241a0292a2844bb5de2.svg?style=flat-square)](https://www.codacy.com/app/SkinnyBot/Skinny)|[![StyleCI](https://styleci.io/repos/73175729/shield)](https://styleci.io/repos/73175729)|[![Latest Stable Version](https://img.shields.io/packagist/v/SkinnyBot/Skinny.svg?style=flat-square)](https://packagist.org/packages/skinnybot/skinny)|[![Total Downloads](https://img.shields.io/packagist/dt/skinnybot/skinny.svg?style=flat-square)](https://packagist.org/packages/skinnybot/skinny)|[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://packagist.org/packages/skinnybot/skinny)

A framework to create discord bot in PHP using [DiscordPHP](https://github.com/teamreflex/DiscordPHP).

# Note
This is the core of the Bot. The skeleton of the application can be found [there](https://github.com/SkinnyBot/Skinny-Skeleton).

# Installation
If you just want to use and/or develop your own bot, you should use the [Skinny Skeleton](https://github.com/SkinnyBot/Skinny-Skeleton) as a base for your project. Installation steps can be found there.

# Requirements
* :package: [Composer](https://getcomposer.org)
* ![PHP](https://img.shields.io/badge/PHP->=5.6-44CB12.svg?style=flat-square)

# Documentation
## Summary
- [Core](#core)
    - [Creating news Modules](#creating-news-modules)
    - [The variable `$message`](#the-variable-message)
    - [The object `$wrapper`](#the-object-wrapper)
    - [The Module System](#the-module-system)
- [Plugins](#plugins)
    - [Creating a Plugin with composer](#creating-a-plugin-with-composer)
    - [Creating a Plugin without composer](#creating-a-plugin-without-composer)
- [Others](#others)
    - [Core plugins list](#core-plugins-list)

### Core
#### Creating news Modules
The bot come with a Module system and a Module manager that allow you to create Modules for your custom commands. Your module must implement the `Skinny\Module\ModuleInterface`.

Here is the default template for a module, named `Basic` for example :

**src/Module/Modules/Basic.php**
```php
<?php
namespace Bot\Module\Modules;

use Skinny\Module\ModuleInterface;
use Skinny\Network\Wrapper;

class Basic implements ModuleInterface
{

    /**
     * {@inheritDoc}
     *
     * @param \Skinny\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onChannelMessage(Wrapper $wrapper, $message)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @param \Skinny\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onPrivateMessage(Wrapper $wrapper, $message)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @param \Skinny\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onCommandMessage(Wrapper $wrapper, $message)
    {
    }
}
```
With these 3 functions you can handle every messages on discord :
* **Command Message** : A normal message in a channel *WITH* a valid command.
* **Private Message** : A private message.
* **Channel Message** : A normal message in a channel *WITHOUT* a valid command.

For example if we want to do a `!say [text]` command, we could do that in the `onCommandMessage` function :
```php
public function onCommandMessage(Wrapper $wrapper, $message)
{
    switch ($message['command']) {
        case 'say':
            $wrapper->Channel->sendMessage($message['parts'][1]);

            break;
    }
}
```
Then we need to add this command in the `config/commands.php` file :
```php
'say' => [
    'params' => 1,
    'syntax' => 'Say [Message]'
]
```

That's all, you did a `!say` command.

#### The variable `$message`

This variable is created by the class [Skinny\Message\Message](https://github.com/SkinnyBot/Skinny/blob/master/src/Message/Message.php) and is an array.
For example with the phrase `!dev param1 param2 param3 etc`, we will have the following array :
```php
[
    'raw' => '!dev param1 param2 param3 etc',
    'parts' => [
            (int) 0 => '!dev',
            (int) 1 => 'param1 param2 param3 etc'
    ],
    'command' => 'dev',
    'message' => 'param1 param2 param3 etc',
    'commandCode' => '!',
    'arguments' => [
            (int) 0 => 'param1',
            (int) 1 => 'param2',
            (int) 2 => 'param3',
            (int) 3 => 'etc'
    ]
]
```

#### The object `$wrapper`

The object `$wrapper` is an instance of the class [Skinny\Network\Wrapper](https://github.com/SkinnyBot/Skinny/blob/master/src/Network/Wrapper.php) and is used as a wrapper to split all the Discord's classes for a better accessibility and clarity when developing modules.

For example, doing a `debug()` on this object would generate the following output :
```php
object(Skinny\Network\Wrapper) {
    ModuleManager => object(Skinny\Module\ModuleManager) {
        ...
    }
    Message => object(Discord\Parts\Channel\Message) {
        ...
    }
    Channel => object(Discord\Parts\Channel\Channel) {
        ...
    }
    Guild => object(Discord\Parts\Guild\Guild) {
        ...
    }
    Members => object(Discord\Repository\Guild\MemberRepository) {
        ...
    }
}
```

#### The Module System

As i said before, this bot implement a Module system. The Module system work like that in debug mode **only** :
The Module system load the file's contents first, then use `preg_replace()` to replace the original class-name with a random one. After that, its create a copy and include it.

When a message is triggered, the module system will do some tests on it to ensure it's a valid message, then it will dispath it into all the loaded modules.


### Plugins
Yes, you can create plugins for this bot. While i recommend to create a plugin using composer you can also create a plugin without using composer, it can be usefull when you develop a plugin. You can find the demo plugin named `Basic` [here](https://github.com/SkinnyBot/Basic).

#### Creating a Plugin with composer

Creating a plugin with composer is easy. First you must create a `composer.json` file like this :
```json
{
    "name": "skinnybot/basic",
    "description": "A simple plugin for Skinny.",
    "homepage": "https://github.com/SkinnyBot/Basic",
    "keywords": ["discord", "bot", "skinny", "plugin"],
    "type": "skinny-plugin",
    "license": "MIT",
    "require": {
        "php": ">=5.6",
        "skinnybot/skinny": "dev-master"
    },
    "autoload": {
        "psr-4": {
            "Basic\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "BasicTest\\": "tests"
        }
    },
    "minimum-stability": "stable"
}
```
**Note** : The `type` of the composer file **must be** `skinny-plugin`, else your plugin won't work. The hierarchical structure of the files will be like this :
```
/config/
    /bootstrap.php
    /commands.php
/src/
    /Module/
        /Modules/
            /Basic.php
composer.json
```
When you have finished to code your plugin, you must of course publish it on [Packagist](https://packagist.org).



#### Creating a Plugin without composer

When you create a plugin without composer, your plugins must be in the `plugins` folder. Let's create the same Basic plugin without composer, the hierarchical structure of the files will be like this :
```
config/
plugins/
    /Basic/
        /config/
            /bootstrap.php
            /commands.php
        /src/
            /Module/
                /Modules/
                    /Basic.php
src/
tmp/
```
After you have created your plugin, you must tell to composer to do the `dumpautoload` event, so the plugin will be registered in the `vendor/skinny-plugins.php` file and it will update your autoloader :
```
composer dumpautoload
```
After that, you will need to load the plugin in the `config/bootstrap.php` file in your application :
```php
Plugin::load('Basic');
//Or if you're using a bootstrap file :
Plugin::load('Basic', ['bootstrap' => true]);
```

### Others

#### Core plugins list
* **[Module Plugin](https://github.com/SkinnyBot/Module)**
The Module plugin is a module that allow you to manage modules with commands. Installed by default in the Skinny Skeleton.
* **[Basic Plugin](https://github.com/SkinnyBot/Basic)**
This plugin is primary used to show how to create plugin and for testing purpose. Installed by default in the Skinny Skeleton.


# Contribute
[Follow this guide to contribute](https://github.com/SkinnyBot/Skinny/blob/master/.github/CONTRIBUTING.md)

# Special Thanks
* Thanks to the CakePHP team and their awesome [CakePHP Core](https://github.com/cakephp/core) classes used to create the plugin system.
