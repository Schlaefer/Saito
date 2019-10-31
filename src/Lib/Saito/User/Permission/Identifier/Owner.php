<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Permission\Identifier;

use Saito\User\ForumsUserInterface;
use Saito\User\SaitoUser;

class Owner extends Role
{
    protected $type = 'owner';

    /**
     * {@inheritDoc}
     *
     * @param int|ForumsUserInterface $token User to check against
     */
    public function __construct($token)
    {
        if (is_int($token)) {
            $token = new SaitoUser(['id' => $token]);
        }

        parent::__construct($token);
    }
}
