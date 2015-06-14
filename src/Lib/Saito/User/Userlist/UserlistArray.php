<?php

namespace Saito\User\Userlist;

class UserlistArray implements UserlistInterface
{

    protected $_userlist = [];

    /**
     * Set userlist
     *
     * @param array $userlist user-list
     * @return void
     */
    public function set($userlist)
    {
        $this->_userlist = $userlist;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->_userlist;
    }
}
