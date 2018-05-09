<?php

namespace Saito\User\Userlist;

use App\Model\Table\UsersTable;
use Saito\RememberTrait;

class UserlistModel
{
    use RememberTrait;

    protected $_User;

    /**
     * Constructor
     *
     * @param UsersTable $User user-table
     */
    public function __construct(UsersTable $User)
    {
        $this->_User = $User;
    }

    /**
     * Returns array with list of usernames
     *
     * @return array usernames
     */
    public function get(): array
    {
        return $this->remember('userlist', function () {
            return $this->_User->userlist();
        });
    }
}
