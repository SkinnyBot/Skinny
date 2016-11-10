# Skinny Bot

|Travis|Scrutinizer|Stable Version|Downloads|License|
|:------:|:-------:|:-------:|:------:|:------:|
|[![Build Status](https://img.shields.io/travis/Xety/Skinny.svg?style=flat-square)](https://travis-ci.org/Xety/Skinny)|[![Scrutinizer](https://img.shields.io/scrutinizer/g/Xety/Skinny.svg?style=flat-square)](https://scrutinizer-ci.com/g/Xety/Skinny)|[![Latest Stable Version](https://img.shields.io/packagist/v/Xety/Skinny.svg?style=flat-square)](https://packagist.org/packages/xety/skinny)|[![Total Downloads](https://img.shields.io/packagist/dt/xety/skinny.svg?style=flat-square)](https://packagist.org/packages/xety/skinny)|[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://packagist.org/packages/xety/skinny)

A simple bot in PHP using [DiscordPHP](https://github.com/teamreflex/DiscordPHP).

# Note
This is the core of the Bot. The skeleton of the application can be found [there](https://github.com/Xety/Skinny-Skeleton).

# Installation
If you just want to use and/or develop your own bot, you should use the [Skinny Skeleton](https://github.com/Xety/Skinny-Skeleton) as a base for your project. Installation steps can be found there.

# Documentation
#### Creating news Modules
The bot come with a Module system and a Module manager that allow you to create Modules for your custom commands.
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
This variable is created by the class [Skinny\Message\Message](https://github.com/Xety/Skinny/blob/master/src/Message/Message.php) and is an array.
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
The object is an instance of the class [Skinny\Network\Wrapper](https://github.com/Xety/Skinny/blob/master/src/Network/Wrapper.php) and is used as a wrapper to split all the Discord's classes for a better accessibility and clarity when developing modules.

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

#### The Module Manager
The Module manager is a module that allow to manage modules with command. That means you can code your own module and load/reload it without restarting the bot. Isn't that cool ?! :laughing:
This module has the following command and it require to be admin of the bot by default :

|Command|Description|Note|
|------|-------|-------|
|`!module load <module>`|Load the specified module.||
|`!module unload <module>`|Unload the specified module.|Only usable in `debug` mode.|
|`!module reload <module>`|Reload the specified module.|Only usable in `debug` mode.|
|`!module time <module>`|Display the time from when the module is loaded.|Display style : `0 days, 1 hours, 38 minutes and 31 seconds`|
|`!module loaded`|Show the list of the loaded modules.|E.g `Modules loaded : Basic, Module, Developer.`|

# Contribute
[Follow this guide to contribute](https://github.com/Xety/Skinny/blob/master/.github/CONTRIBUTING.md)

# Ressources
* [CakePHP](https://github.com/cakephp/cakephp)
