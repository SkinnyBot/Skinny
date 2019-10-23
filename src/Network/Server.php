<?php
namespace Skinny\Network;

use Cake\Chronos\Chronos;
use CharlotteDunois\Yasmin\Client;
use React\EventLoop\Factory;
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
     * @var \CharlotteDunois\Yasmin\Client
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
        Configure::checkTokenKey();
        // Create ReactPHP event loop
        $loop = Factory::create();

        // Create the client
        $this ->Discord = new Client([], $loop);

        //$this->Discord = new Discord(Configure::read('Discord'));

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
        $this->Discord->on('ready', function () {
            debug('Successfully logged into ' . $this->Discord->user->tag);

            $this->Discord->user->setGame(Configure::read('Bot.game'));
            $this->Discord->user->setStatus(Configure::read('Bot.status'));
            $this->Discord->user->setUsername(Configure::read('Bot.username'));
            $this->Discord->user->setAvatar(Configure::read('Bot.avatar'));
        });

        $this->Discord->on('message', function (\CharlotteDunois\Yasmin\Models\Message $message) {
            // Check if the author of the message is not the bot.
            if ($this->Discord->user->id === $message->author->id) {
                return;
            }
            // Parse the message.
            $content = Message::parse($message->content);

            // Initialise the Wrapper.
            $wrapper = Wrapper::getInstance()->setInstances($message, $this->ModuleManager, $this->Discord);

            //Handle the type of the message.
            //Note : The order is very important !
            if ($wrapper->Message->type === 'RECIPIENT_ADD' || $wrapper->Message->type === 'CALL') {
                debug('message privé');
                $this->ModuleManager->onPrivateMessage($wrapper, $content);
            } elseif ($content['commandCode'] === Configure::read('Command.prefix') &&
                        isset(Configure::read('Commands')[$content['command']])) {
                $command = Configure::read('Commands')[$content['command']];

                // Check if the command is an Admin command and if yes,
                //check the permissions of the user. (Authorized for Admins and Developers)
                if ((isset($command['admin']) && $command['admin'] === true) &&
                        !User::hasPermission($wrapper, Configure::read('Discord.admins')) &&
                        !User::hasPermission($wrapper, Configure::read('Discord.developers'))
                    ) {
                    $wrapper->Message->reply(
                        ':octagonal_sign: You are not administrator of the bot. :octagonal_sign:'
                    );

                    return;
                }

                // Check if the command is a Developer command and if yes,
                //check the permissions of the user.
                if ((isset($command['developer']) && $command['developer'] === true) &&
                        !User::hasPermission($wrapper, Configure::read('Discord.developers'))) {
                    $wrapper->Message->reply(
                        ':octagonal_sign: You are not a developer of the bot. :octagonal_sign:'
                    );

                    return;
                }

                // Check the syntax of the command.
                if (count($content['arguments']) < $command['params']) {
                    $wrapper->Message->reply(Command::syntax($content));

                    return;
                }

                $this->ModuleManager->onCommandMessage($wrapper, $content);
            } else {
                $this->ModuleManager->onChannelMessage($wrapper, $content);
            }
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
        $this->Discord->login(Configure::read('Bot.token'));

        $this->Discord->loop->run();
    }
}
