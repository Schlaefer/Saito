<?php

namespace App\Auth;

use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;

/**
 * mylittleforum 1 md5 passwords.
 */
class MlfAuthenticate extends FormAuthenticate
{
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $config['passwordHasher'] = 'Mlf';
        parent::__construct($registry, $config);
    }
}
