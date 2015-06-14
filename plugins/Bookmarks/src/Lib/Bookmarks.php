<?php

namespace Bookmarks\Lib;

use App\Controller\Component\CurrentUserComponent;
use Cake\ORM\TableRegistry;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * Class Bookmarks handles bookmarks for a CurrentUser
 */
class Bookmarks
{
    /**
     * @var bookmarks format: [entry_id => id, …]
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
        $this->_bookmarks = TableRegistry::get('Bookmarks')
            ->find('list', ['keyField' => 'entry_id', 'valueField' => 'id'])
            ->where(['user_id' => $this->_CurrentUser->getId()])
            ->toArray();
    }
}
