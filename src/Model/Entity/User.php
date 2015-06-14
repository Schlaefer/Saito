<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Saito\User\SaitoUser;

class User extends Entity
{

    protected $cache = [];

    /**
     * Get number of postings
     *
     * @return mixed
     */
    public function numberOfPostings()
    {
        return $this->get('entry_count');
    }

    /**
     * get user rolse
     *
     * @return string
     */
    public function getRole()
    {
        // @todo 3.0 better implementation
        $user = new SaitoUser($this);

        return $user->getRole();
    }

    /**
     * Check if user is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        // @todo 3.0 better implementation
        $user = new SaitoUser($this);

        return $user->isForbidden();
    }
}
