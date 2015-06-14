<?php

namespace Saito\User\Blocker;

use Cake\ORM\Table;

abstract class BlockerAbstract
{

    protected $Table;

    /**
     * block user
     *
     * @param int $userId user-ID
     * @param array $options options
     * @return bool
     */
    abstract public function block($userId, array $options = []);

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
