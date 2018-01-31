<?php

namespace Saito\Posting\Decorator;

use Cake\Core\Configure;
use Saito\Posting\PostingInterface;
use Saito\User\CurrentUserInterface;
use Saito\User\ForumsUserInterface;

trait UserPostingTrait
{

    protected $_cache = [];

    protected $_CurrentUser;

    /**
     * Get current-user.
     *
     * @return CurrentUser
     */
    public function getCurrentUser()
    {
        return $this->_CurrentUser;
    }

    /**
     * Set current user.
     *
     * @param CurrentUserInterface $CU  current user
     * @return void
     */
    public function setCurrentUser($CU)
    {
        $this->_CurrentUser = $CU;
    }

    /**
     * Checks if answering an entry is allowed
     *
     * @return bool
     */
    public function isAnsweringForbidden()
    {
        if ($this->isLocked()) {
            return 'locked';
        }
        $permission = $this->_CurrentUser->Categories->permission('answer', $this->get('category'));

        return !$permission;
    }

    /**
     * checks if entry is bookmarked by current user
     *
     * @return bool
     */
    public function isBookmarked()
    {
        return $this->_CurrentUser->hasBookmarked($this->get('id'));
    }

    /**
     * Check if editing is forbidden.
     *
     * @return bool|string true or string if forbidden, false if allowed
     */
    public function isEditingAsCurrentUserForbidden()
    {
        return $this->_isEditingForbidden($this, $this->_CurrentUser);
    }

    /**
     * Check if editing as user is forbidden.
     *
     * @return bool|string true or string if forbidden, false if allowed
     */
    public function isEditingWithRoleUserForbidden()
    {
        $MockedUser = clone $this->_CurrentUser;
        $MockedUser->set('user_type', 'user');

        return $this->_isEditingForbidden($this, $MockedUser);
    }

    /**
     * Check if editing on the posting is forbidden.
     *
     * @param PostingInterface $posting The posting.
     * @param ForumsUserInterface $User The user.
     * @return bool|string string if a reason is available.
     */
    protected function _isEditingForbidden(PostingInterface $posting, ForumsUserInterface $User)
    {
        if ($User->isLoggedIn() !== true) {
            return true;
        } elseif ($User->permission('saito.core.posting.edit.unrestricted')) {
            return false;
        }

        $editPeriod = Configure::read('Saito.Settings.edit_period') * 60;
        $timeLimit = $editPeriod + ($posting->get('time')->format('U'));
        $isOverTime = time() > $timeLimit;

        $isOwn = $User->isUser($posting->get('user_id'));

        if ($User->permission('saito.core.posting.edit.restricted')) {
            if ($isOwn && $isOverTime && !$posting->isPinned()) {
                return 'time';
            } else {
                return false;
            }
        }

        if (!$isOwn) {
            return 'user';
        } elseif ($isOverTime) {
            return 'time';
        } elseif ($this->isLocked()) {
            return 'locked';
        }

        return false;
    }

    /**
     * Check if posting is ignored by user.
     *
     * @return bool
     */
    public function isIgnored()
    {
        return $this->_CurrentUser->ignores($this->get('user_id'));
    }

    /**
     * Check if posting is unread to user.
     *
     * @return bool
     */
    public function isUnread()
    {
        if (!isset($this->_cache['isUnread'])) {
            $id = $this->get('id');
            $time = $this->get('time');
            $this->_cache['isUnread'] = !$this->getCurrentUser()
                ->ReadEntries->isRead($id, $time);
        }

        return $this->_cache['isUnread'];
    }

    /**
     * Checks if posting has newer answers
     *
     * currently only supported for root postings
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function hasNewAnswers()
    {
        if (!$this->isRoot()) {
            throw new \RuntimeException(
                'Posting with id ' . $this->get('id') . ' is no root posting.'
            );
        }
        if (!$this->_CurrentUser->get('last_refresh')) {
            return false;
        }

        return $this->_CurrentUser->get('last_refresh_unix') < strtotime(
            $this->get('last_answer')
        );
    }
}
