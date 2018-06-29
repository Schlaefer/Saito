<?php

namespace Saito\User\Userlist;

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;

/**
 * Lazy load list of all users and cache
 */
class UserlistModel
{
    use RememberTrait;

    /**
     * Returns array with list of usernames
     *
     * @return array usernames
     */
    public function get(): array
    {
        return $this->remember('userlist', function () {
            /** @var UsersTable $users */
            $users = TableRegistry::get('Users');

            return $users->userlist();
        });
    }
}
