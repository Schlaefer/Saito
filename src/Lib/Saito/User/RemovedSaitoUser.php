<?php

namespace Saito\User;

class RemovedSaitoUser extends SaitoUser
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $settings = [
            'id' => null,
            'username' => __('user.removed.placeholder')
        ];
        parent::__construct($settings);
    }
}
