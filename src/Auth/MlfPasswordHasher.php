<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;
use Cake\Utility\Security;

/**
 * mylittleforum 1.x unsalted md5 passwords.
 */
class MlfPasswordHasher extends AbstractPasswordHasher
{

    /**
     * {@inheritDoc}
     */
    public function hash($password)
    {
        return Security::hash($password, 'md5', false);
    }

    /**
     * {@inheritDoc}
     */
    public function check($password, $hash)
    {
        return $hash === self::hash($password);
    }
}
