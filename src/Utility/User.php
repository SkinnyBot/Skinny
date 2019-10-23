<?php
namespace Skinny\Utility;

class User
{

    /**
     * Checks if the given user has permission to perform an action.
     *
     * @param int $wrapper The wrapper instance.
     * @param array $admins Admins of the bot.
     *
     * @return bool
     */
    public static function hasPermission($wrapper, array $admins = [])
    {
        //Check the user id
        if (in_array($wrapper->Message->author->id, $admins)) {
            return true;
        }

        $roles = $wrapper->Members->resolve($wrapper->Message->author->id)->roles->keys();

        foreach ($roles as $id => $role) {
            if (in_array($role, $admins)) {
                return true;
            }
        }
        return false;
    }
}
