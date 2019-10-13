<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\CurrentUser;

use Saito\User\Categories;
use Saito\User\ForumsUserInterface;
use Saito\User\LastRefresh\LastRefreshInterface;
use Saito\User\Permission;
use Saito\User\ReadPostings\ReadPostingsInterface;

interface CurrentUserInterface extends ForumsUserInterface
{
    /**
     * Setter for last refresh
     *
     * @param LastRefreshInterface $lastRefresh last refresh
     * @return self
     */
    public function setLastRefresh(LastRefreshInterface $lastRefresh): CurrentUserInterface;

    /**
     * Getter for last refresh
     *
     * @return LastRefreshInterface
     */
    public function getLastRefresh(): LastRefreshInterface;

    /**
     * Setter for read postings
     *
     * @param ReadPostingsInterface $readPostings read postings
     * @return self
     */
    public function setReadPostings(ReadPostingsInterface $readPostings): CurrentUserInterface;

    /**
     * Getter for read postings
     *
     * @return ReadPostingsInterface
     */
    public function getReadPostings(): ReadPostingsInterface;

    /**
     * Setter for categories
     *
     * @param Categories $categories categories
     * @return self
     */
    public function setCategories(Categories $categories): CurrentUserInterface;

    /**
     * Gets Categories
     *
     * @return Categories
     */
    public function getCategories(): Categories;

    /**
     * Has current-user bookmarked posting
     *
     * @param int $postingId posting-ID
     * @return bool
     */
    public function hasBookmarked($postingId);

    /**
     * checks if current user ignores user or get all ignored users
     *
     * @param null|int $userId user-ID
     * @return bool|array
     */
    public function ignores($userId);

    /**
     * Checks if the user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * Check if user has permission to access a resource.
     *
     * @param string $resource resource
     * @return bool
     */
    public function permission(string $resource): bool;

    /**
     * Get permissions
     *
     * @return Permission
     */
    public function getPermissions(): Permission;
}
