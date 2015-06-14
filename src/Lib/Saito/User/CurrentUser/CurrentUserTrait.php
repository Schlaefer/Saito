<?php

namespace Saito\User\CurrentUser;

use Bookmarks\Lib\Bookmarks;

/**
 * Main implementor of CurrentUserInterface.
 */
trait CurrentUserTrait
{
    /**
     * @var Bookmarks bookmarks of the current user
     */
    protected $_Bookmarks;

    /**
     * {@inheritDoc}
     */
    public function hasBookmarked($postingId)
    {
        if ($this->_Bookmarks === null) {
            $this->_Bookmarks = new Bookmarks($this);
        }
        return $this->_Bookmarks->isBookmarked($postingId);
    }

    /**
     * {@inheritDoc}
     */
    public function ignores($userId = null)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return isset($this->_settings['ignores'][$userId]);
    }
}
