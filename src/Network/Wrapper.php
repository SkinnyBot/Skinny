<?php
namespace Bot\Network;

use Bot\Singleton\Singleton;

/**
 * This class is a wrapper to separate all the Discord classes into variables
 * for a better accessibility and clarity when developing modules.
 */
class Wrapper extends Singleton
{
    /**
     * The ModuleManager instance.
     *
     * @var \Bot\Module\ModuleManager
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

    public function setInstances($message, $moduleManager)
    {
        $this->ModuleManager = $moduleManager;
        $this->Message = $message;
        $this->Channel = $message->channel;
        $this->Guild = $message->channel->guild;

        if (is_object($message->channel->guild->members)) {
            $this->Members = $message->channel->guild->members;
            debug($message->channel->guild->members);
        }

        return $this;
    }
}
