<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Auth;

use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;

/**
 * mylittleforum 2.x auth with salted sha1 passwords.
 */
class Mlf2Authenticate extends FormAuthenticate
{
    /**
     * constructor.
     *
     * @param ComponentRegistry $registry component registry
     * @param array             $config   config
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $config['passwordHasher'] = 'Mlf2';
        parent::__construct($registry, $config);
    }
}
