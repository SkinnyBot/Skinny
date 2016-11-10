<?php
namespace Skinny\Module;

use Skinny\Network\Wrapper;

interface ModuleInterface
{
    /**
     * Called when a message is posted in a channel.
     *
     * @param \Skinny\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onChannelMessage(Wrapper $wrapper, $message);

    /**
     * Called when a command is posted in a channel.
     *
     * @param \Skinny\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onCommandMessage(Wrapper $wrapper, $message);

    /**
     * Called when someone has send a private message to the bot.
     *
     * @param \Skinny\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onPrivateMessage(Wrapper $wrapper, $message);
}
