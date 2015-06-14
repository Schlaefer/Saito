<?php

namespace Saito\User\Userlist;

use App\Model\Table\UsersTable;

class UserlistModel implements UserlistInterface
{

    protected $_userlist = [];

    protected $_User;

    /**
     * Setter for user-table.
     *
     * @param UsersTable $User user-table
     * @return void
     */
    public function set(UsersTable $User)
    {
        $this->_User = $User;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        if (empty($this->_userlist)) {
            $this->_userlist = $this->_User->userlist();
        }

        return $this->_userlist;
    }
}
