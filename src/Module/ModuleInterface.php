<?php
namespace Bot\Module;

use Bot\Network\Wrapper;

interface ModuleInterface
{
    /**
     * Called when a message is posted in a channel.
     *
     * @param \Bot\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onChannelMessage(Wrapper $wrapper, $message);

    /**
     * Called when a command is posted in a channel.
     *
     * @param \Bot\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onCommandMessage(Wrapper $wrapper, $message);

    /**
     * Called when someone has send a private message to the bot.
     *
     * @param \Bot\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onPrivateMessage(Wrapper $wrapper, $message);
}
