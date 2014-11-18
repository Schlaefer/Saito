<?php

namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;
use Cake\Utility\Security;

/**
 * mylittleforum 1.x unsalted md5 passwords.
 */
class MlfPasswordHasher extends AbstractPasswordHasher
{
    public function hash($password)
    {
        return Security::hash($password, 'md5', false);
    }

    public function check($password, $hash)
    {
        return $hash === self::hash($password);
    }
}
