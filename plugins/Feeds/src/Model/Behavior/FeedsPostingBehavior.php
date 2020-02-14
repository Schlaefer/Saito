<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Feeds\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;

class FeedsPostingBehavior extends Behavior
{
    /**
     * Implements the custom find type 'feed'
     *
     * Add parameters for generating a rss/json-feed with find('feed', …)
     *
     * @param \Cake\ORM\Query $query query
     * @return \Cake\ORM\Query
     */
    public function findFeed(Query $query)
    {
        return $query->contain('Users')
            ->order(['last_answer' => 'DESC'])
            ->limit(10);
    }
}
