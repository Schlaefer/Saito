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
class SlidetabUserpostsCell extends SlidetabCell
{

    /**
     * {@inheritDoc}
     */
    public function display()
    {
        $CurrentUser = Registry::get('CU');
        $Entries = TableRegistry::get('Entries');
        $recentPosts = $Entries->getRecentEntries(
            $CurrentUser,
            [
                'user_id' => $CurrentUser->getId(),
                'limit' => 5
            ]
        );
        $this->set(compact('recentPosts'));
    }

    /**
     * {@inheritDoc}
     */
    protected function _getSlidetabId()
    {
        return 'recentposts';
    }
}
