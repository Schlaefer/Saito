<?php

namespace App\View\Cell;

use Saito\Shouts\ShoutsDataTrait;
use Saito\View\Cell\SlidetabCell;

class SlidetabShoutboxCell extends SlidetabCell
{

    use ShoutsDataTrait;

    /**
     * {@inheritDoc}
     */
    public function display()
    {
        $this->set('shouts', $this->getShouts());
    }

    /**
     * {@inheritDoc}
     */
    protected function _getSlidetabId()
    {
        return 'shoutbox';
    }
}
