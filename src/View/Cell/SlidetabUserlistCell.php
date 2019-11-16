<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Cell;

use Saito\App\Registry;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\View\Cell\SlidetabCell;

class SlidetabUserlistCell extends SlidetabCell
{

    protected $_validCellOptions = [];

    /**
     * {@inheritDoc}
     */
    public function display(CurrentUserInterface $CurrentUser)
    {
        /* @var \Saito\App\Stats $stats */
        $stats = Registry::get('AppStats');
        $this->set('CurrentUser', $CurrentUser);
        $this->set('online', $stats->getRegistredUsersOnline());
        $this->set('registered', $stats->getNumberOfRegisteredUsersOnline());
    }

    /**
     * {@inheritDoc}
     */
    protected function _getSlidetabId()
    {
        return 'userlist';
    }
}
