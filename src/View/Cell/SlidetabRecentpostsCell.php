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

use Cake\ORM\TableRegistry;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\View\Cell\SlidetabCell;

/**
 * Slidetab for current users's recent postings
 *
 * @package App\View\Cell
 */
class SlidetabRecentpostsCell extends SlidetabCell
{
    /**
     * {@inheritDoc}
     */
    public function display(CurrentUserInterface $CurrentUser)
    {
        /**
         * @var \App\Model\Table\EntriesTable
         */
        $Entries = TableRegistry::getTableLocator()->get('Entries');
        $recentEntries = $Entries->getRecentPostings($CurrentUser);
        $this->set(compact('recentEntries', 'CurrentUser'));
    }

    /**
     * {@inheritDoc}
     */
    protected function _getSlidetabId()
    {
        return 'recententries';
    }
}
