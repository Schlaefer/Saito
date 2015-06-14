<?php

namespace App\View\Cell;

use Cake\ORM\TableRegistry;
use Saito\App\Registry;
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
    public function display()
    {
        $CurrentUser = Registry::get('CU');
        $Entries = TableRegistry::get('Entries');
        $recentEntries = $Entries->getRecentEntries($CurrentUser);
        $this->set(compact('recentEntries'));
    }

    /**
     * {@inheritDoc}
     */
    protected function _getSlidetabId()
    {
        return 'recententries';
    }
}
