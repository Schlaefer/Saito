<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Posting\UserPosting;

use Cake\Core\Configure;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\Permission\ResourceAI;

/**
 * Implements UserPostingInterface
 */
trait UserPostingTrait
{
    /**
     * @var array
     */
    private $_userPostingTraitUnreadCache = [];

    /**
     * @var CurrentUserInterface
     */
    private $_CurrentUser;

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser(): CurrentUserInterface
    {
        return $this->_CurrentUser;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentUser(CurrentUserInterface $CurrentUser): void
    {
        $this->_CurrentUser = $CurrentUser;
    }

    /**
     * {@inheritDoc}
     */
    public function isAnsweringForbidden()
    {
        // @bogus Locked isn't a user property
        if ($this->isLocked()) {
            return 'locked';
        }
        $permission = $this->_CurrentUser->getCategories()->permission('answer', $this->get('category'));

        return !$permission;
    }

    /**
     * {@inheritDoc}
     */
    public function isBookmarked(): bool
    {
        return $this->_CurrentUser->hasBookmarked($this->get('id'));
    }

    /**
     * {@inheritDoc}
     */
    public function isEditingAllowed(): bool
    {
        return $this->_isEditingAllowed($this, $this->_CurrentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function isEditingAsUserAllowed(): bool
    {
        $MockedUser = clone $this->_CurrentUser;
        $MockedUser->set('user_type', 'user');

        return $this->_isEditingAllowed($this, $MockedUser);
    }

    /**
     * Check if editing on the posting is forbidden.
     *
     * @param BasicPostingInterface $posting The posting.
     * @param CurrentUserInterface $User The user.
     * @return bool|string string if a reason is available.
     */
    protected function _isEditingAllowed(BasicPostingInterface $posting, CurrentUserInterface $User)
    {
        if ($User->isLoggedIn() !== true) {
            return false;
        }

        if ($User->permission('saito.core.posting.edit.unrestricted')) {
            return true;
        }

        /// Check category
        $action = $posting->isRoot() ? 'thread' : 'answer';
        $categoryAllowed = $User->getCategories()
            ->permission($action, $posting->get('category_id'));
        if (!$categoryAllowed) {
            return false;
        }

        $editPeriod = Configure::read('Saito.Settings.edit_period') * 60;
        $timeLimit = $editPeriod + ($posting->get('time')->format('U'));
        $isOverTime = time() > $timeLimit;

        $isOwn = $User->permission(
            'saito.core.posting.edit',
            (new ResourceAI())->onOwner($posting->get('user_id'))
        );

        if (!$isOverTime && $isOwn && !$this->isLocked()) {
            // Normal posting without special conditions.
            return true;
        }

        if ($User->permission('saito.core.posting.edit.restricted')) {
            if (!$isOwn || $posting->isPinned()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isIgnored(): bool
    {
        return $this->_CurrentUser->ignores($this->get('user_id'));
    }

    /**
     * {@inheritDoc}
     */
    public function isUnread(): bool
    {
        if (!isset($this->_userPostingTraitUnreadCache['isUnread'])) {
            $id = $this->get('id');
            $time = $this->get('time');
            $this->_userPostingTraitUnreadCache['isUnread'] = !$this->getCurrentUser()
                ->getReadPostings()->isRead($id, $time);
        }

        return $this->_userPostingTraitUnreadCache['isUnread'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasNewAnswers(): bool
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
