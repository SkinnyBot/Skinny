<?php
namespace Skinny\Utility;

use Skinny\Network\Wrapper ;

class User
{

    /**
     * Checks if the given user has permission to perform an action.
     *
     * @param \Skinny\Network\Wrapper $wrapper The wrapper instance.
     * @param array $authorized Authorized users/roles for the permissions.
     *
     * @return bool Whether the user has the permission or not.
     */
    public static function hasPermission(Wrapper $wrapper, array $authorized = []) : bool
    {
        //Check the user id
        if (in_array($wrapper->Message->author->id, $authorized)) {
            return true;
        }

        $roles = $wrapper->Members->resolve($wrapper->Message->author->id)->roles->keys();

        foreach ($roles as $id => $role) {
            if (in_array($role, $authorized)) {
                return true;
            }
        }
        return false;
    }
}
