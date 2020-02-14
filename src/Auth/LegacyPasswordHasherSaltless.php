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

use Authentication\PasswordHasher\LegacyPasswordHasher;
use Cake\Utility\Security;

/**
 * Check legacy passwords but without using Cake's salt
 */
class LegacyPasswordHasherSaltless extends LegacyPasswordHasher
{
    /**
     * {@inheritDoc}
     */
    public function hash($password): string
    {
        return Security::hash($password, $this->_config['hashType'], false);
    }
}
