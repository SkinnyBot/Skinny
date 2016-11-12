<?php
namespace Skinny\Network;

use Discord\Discord;
use Skinny\Core\Configure;
use Skinny\Message\Message;
use Skinny\Module\ModuleManager;
use Skinny\Network\Wrapper;
use Skinny\Utility\Command;
use Skinny\Utility\User;

class Server
{
    /**
     * The Discord instance.
     *
     * @var \Discord\Discord
     */
    public $Discord;

    /**
     * The Module Manager instance.
     *
     * @var \Skinny\Module\ModuleManager
     */
    public $ModuleManager;

    /**
     * Initialize the Bot and and the ModuleManager.
     */
    public function __construct()
    {
        $this->Discord = new Discord(Configure::read('Discord'));

        //Initialize the ModuleManager.
        $modulesPriorities = [];
        if (Configure::check('Modules.priority')) {
            $modulesPriorities = Configure::read('Modules.priority');
        }

        $this->ModuleManager = new ModuleManager($modulesPriorities);
    }

    /**
     * Handle the events.
     *
     * @return void
     */
    public function listen()
    {
        $this->Discord->on('ready', function ($discord) {

            // Listen for events here
            $discord->on('message', function ($message) {
                $content = Message::getInstance()->setData($message->content);
                $wrapper = Wrapper::getInstance()->setInstances($message, $this->ModuleManager);

                //Ignore the message if the message author is the Bot. Prevent an infinite loop.
                if ($this->Discord->id === $wrapper->Message->author->id) {
                    return;
                }

                //Handle the type of the message.
                //Note : The order is very important !

                //Test if the message is a private message.
                if ($wrapper->Channel->is_private === true) {
                    $this->ModuleManager->onPrivateMessage($wrapper, $content);

                //Test if the message is a command and if the command exist.
                } elseif ($content['commandCode'] === Configure::read('Command.prefix') &&
                            isset(Configure::read('Commands')[$content['command']])) {
                    $command = Configure::read('Commands')[$content['command']];

                    //Verify if the command is an admin command and if the user has the permission to use it.
                    if ((isset($command['admin']) && $command['admin'] === true) &&
                            !User::hasPermission($wrapper->Message->author->id, Configure::read('Bot.admins'))) {
                        $wrapper->Message->reply('You are not administrator of the bot.');

                        return;
                    }

                    //Check if the user has given enough parameters.
                    if (count($content['arguments']) < $command['params']) {
                        $wrapper->Message->reply(Command::syntax($content));

                        return;
                    }

                    $this->ModuleManager->onCommandMessage($wrapper, $content);

                //Else it's a simple message.
                } else {
                    $this->ModuleManager->onChannelMessage($wrapper, $content);
                }
            });
        });
    }

    /**
     * Run the bot.
     *
     * @return void
     */
    public function startup()
    {
        $this->listen();

        $this->Discord->run();
    }
}
