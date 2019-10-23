<?php
namespace Skinny\Network;

use Skinny\Core\Configure;
use Skinny\Singleton\Singleton;

/**
 * This class is a wrapper to separate all the Discord classes into variables
 * for a better accessibility and clarity when developing modules.
 */
class Wrapper extends Singleton
{
    /**
     * The ModuleManager instance.
     *
     * @var \Skinny\Module\ModuleManager
     */
    public $ModuleManager;

    /**
     * The Message instance.
     *
     * @var \CharlotteDunois\Yasmin\Models\Message
     */
    public $Message;

    /**
     * The Channel instance.
     *
     * @var \CharlotteDunois\Yasmin\Models\TextChannel
     */
    public $Channel;

    /**
     * The Guild instance.
     *
     * @var \CharlotteDunois\Yasmin\Models\Guild
     */
    public $Guild;

    /**
     * The Members instance.
     *
     * @var \CharlotteDunois\Yasmin\Models\GuildMemberStorage
     */
    public $Members;

    /**
     * Set the instances to the Wrapper.
     *
     * @param \Discord\Parts\Channel\Message $message The messages object.
     * @param \Skinny\Module\ModuleManager $moduleManager The ModuleManager object.
     *
     * @return object Return this Wrapper.
     */
    public function setInstances($message, $moduleManager, $discord)
    {
        $this->ModuleManager = $moduleManager;
        $this->Message = $message;
        $this->Channel = $message->channel;
        $this->Guild = $discord->guilds->resolve(Configure::read('Discord.guild'));
        $this->Members = $this->Guild->members;

        return $this;
    }
}
