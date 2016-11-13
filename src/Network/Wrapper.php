<?php
namespace Skinny\Network;

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
     * @var \Discord\Parts\Channel\Message
     */
    public $Message;

    /**
     * The ModuleManager instance.
     *
     * @var \Discord\Parts\Channel\Channel
     */
    public $Channel;

    /**
     * The ModuleManager instance.
     *
     * @var \Discord\Parts\Guild\Guild
     */
    public $Guild;

    /**
     * The ModuleManager instance.
     *
     * @var \Discord\Repository\Guild\MemberRepository
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
    public function setInstances($message, $moduleManager)
    {
        $this->ModuleManager = $moduleManager;
        $this->Message = $message;
        $this->Channel = $message->channel;

        if (isset($message->channel->guild) && is_object($message->channel->guild)) {
            $this->Guild = $message->channel->guild;
        }

        if (isset($message->channel->guild->members) && is_object($message->channel->guild->members)) {
            $this->Members = $message->channel->guild->members;
        }

        return $this;
    }
}
