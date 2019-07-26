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

/**
 * Posting properties derived from the current user
 */
interface UserPostingInterface
{
    /**
     * Checks if answering an entry is allowed
     *
     * @return bool|string
     */
    public function isAnsweringForbidden();

    /**
     * checks if entry is bookmarked by current user
     *
     * @return bool
     */
    public function isBookmarked(): bool;

    /**
     * Check if editing is forbidden.
     *
     * @return bool|string true or string if forbidden, false if allowed
     */
    public function isEditingAsCurrentUserForbidden();

    /**
     * Check if editing as user is forbidden.
     *
     * @return bool|string true or string if forbidden, false if allowed
     */
    public function isEditingWithRoleUserForbidden();

    /**
     * Check if posting is ignored by user.
     *
     * @return bool
     */
    public function isIgnored(): bool;

    /**
     * Check if posting is unread to user.
     *
     * @return bool
     */
    public function isUnread(): bool;

    /**
     * Checks if posting has newer answers
     *
     * currently only supported for root postings
     *
     * @return bool
     */
    public function hasNewAnswers(): bool;
}
