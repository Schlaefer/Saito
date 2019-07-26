<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Bookmarks\Lib;

use Bookmarks\Model\Table\BookmarksTable;
use Cake\ORM\TableRegistry;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * Class Bookmarks handles bookmarks for a CurrentUser
 */
class Bookmarks
{
    /**
     * @var array format: [entry_id => id, …]
     */
    protected $_bookmarks;

    /**
     * @var CurrentUserInterface
     */
    protected $_CurrentUser;

    /**
     * Constructor.
     *
     * @param CurrentUserInterface $CurrentUser CurrentUser
     */
    public function __construct(CurrentUserInterface $CurrentUser)
    {
        $this->_CurrentUser = $CurrentUser;
    }

    /**
     * Check if posting is bookmarked by the CurrentUser
     *
     * @param int $postingId posting-ID
     * @return bool
     */
    public function isBookmarked($postingId)
    {
        if ($this->_bookmarks === null) {
            $this->_load();
        }

        return isset($this->_bookmarks[$postingId]);
    }

    /**
     * Get all bookmarks for the CurrentUser.
     *
     * @return void
     */
    protected function _load()
    {
        $this->_bookmarks = [];
        if (!$this->_CurrentUser->isLoggedIn()) {
            $this->_bookmarks = [];

            return;
        }
        /** @var BookmarksTable */
        $BookmarksTable = TableRegistry::get('Bookmarks.Bookmarks');
        $this->_bookmarks = $BookmarksTable
            ->find('list', ['keyField' => 'entry_id', 'valueField' => 'id'])
            ->where(['user_id' => $this->_CurrentUser->getId()])
            ->toArray();
    }
}
