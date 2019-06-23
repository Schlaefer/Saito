<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Blocker;

use Cake\ORM\Table;

abstract class BlockerAbstract
{
    /** @var Table UserBlocks table */
    protected $Table;

    /**
     * block user
     *
     * @param int $userId user-ID of the user to block
     * @return bool success
     */
    abstract public function block(int $userId): bool;

    /**
     * id for reason why user is blocked
     *
     * in plugin use <domain>.<id>
     *
     * @return string
     */
    abstract public function getReason();

    /**
     * Set user block table
     *
     * @param Table $Table table
     * @return void
     */
    public function setUserBlockTable($Table)
    {
        $this->Table = $Table;
    }
}
