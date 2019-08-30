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
 * mylittleforum 1 md5 passwords.
 */
class MlfAuthenticate extends FormAuthenticate
{
    /**
     * {@inheritDoc}
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        $config['passwordHasher'] = 'Mlf';
        parent::__construct($registry, $config);
    }
}
