<?php
namespace Skinny\Utility;

class User
{

    /**
     * Checks if the given user has permission to perform an action.
     *
     * @param int $user The user id to check.
     * @param array $admins Admins of the bot.
     *
     * @return bool
     */
    public static function hasPermission($user, array $admins = [])
    {
        return in_array($user, $admins);
    }
}
