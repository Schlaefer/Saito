<?php

namespace Saito\User\Blocker;

abstract class BlockerAbstract
{

    protected $Table;

    public abstract function block($userId, array $options = []);

    /**
     * id for reason why user is blocked
     *
     * in plugin use <domain>.<id>
     *
     * @return string
     */
    public abstract function getReason();

    public function setUserBlockTable($Table)
    {
        $this->Table = $Table;
    }

}
