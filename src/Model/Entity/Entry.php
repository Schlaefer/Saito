<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Saito\App\Registry;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Basic\BasicPostingTrait;
use Saito\Posting\PostingInterface;

class Entry extends Entity implements BasicPostingInterface
{
    use BasicPostingTrait;

    /**
     * Convert entity to posting
     *
     * @return PostingInterface
     */
    public function toPosting()
    {
        return Registry::newInstance(
            '\Saito\Posting\Posting',
            ['rawData' => $this->toArray()]
        );
    }
}
