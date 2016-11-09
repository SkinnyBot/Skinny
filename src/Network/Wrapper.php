<?php
namespace Bot\Network;

use Bot\Singleton\Singleton;

class Wrapper extends Singleton
{
    public $ModuleManager;

    public $Message;

    public $Channel;

    public $Guild;

    public $Members;

    public function setInstances($message, $moduleManager)
    {
        $this->ModuleManager = $moduleManager;
        $this->Message = $message;
        $this->Channel = $message->channel;
        $this->Guild = $message->channel->guild;

        if (isset($message->channel->guild->members)) {
            $this->Members = $message->channel->guild->members;
        }

        return $this;
    }
}
