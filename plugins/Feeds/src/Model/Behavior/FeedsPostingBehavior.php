<?php

namespace Feeds\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Saito\App\Registry;

class FeedsPostingBehavior extends Behavior
{
    /**
     * Implements the custom find type 'feed'
     *
     * Add parameters for generating a rss/json-feed with find('feed', …)
     *
     * @param Query $query query
     * @return Query
     */
    public function findFeed(Query $query)
    {
        $CurrentUser = Registry::get('CU');

        return $query->contain('Users')
            ->where(['category_id IN' => $CurrentUser->Categories->getAll('read')])
            ->limit(10);
    }
}
