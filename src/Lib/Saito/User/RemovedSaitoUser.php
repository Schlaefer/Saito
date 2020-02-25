<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
            'username' => __('user.removed.placeholder'),
        ];
        parent::__construct($settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getRole(): string
    {
        return 'deletedUser';
    }
}
