<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\LastRefresh;

use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * handles last refresh time for the current user
 */
abstract class LastRefreshAbstract implements LastRefreshInterface
{

    /**
     * @var CurrentUserInterface
     */
    protected $_CurrentUser;

    /**
     * @var mixed storage object depending on implementation
     */
    protected $_storage;

    /**
     * Constructor
     *
     * @param CurrentUserInterface $CurrentUser current-user
     * @param mixed $storage storage a storage handler
     */
    public function __construct(CurrentUserInterface $CurrentUser, $storage = null)
    {
        $this->_CurrentUser = $CurrentUser;
        $this->_storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function isNewerThan($timestamp): ?bool
    {
        $lastRefresh = $this->_get();
        if ($lastRefresh === null) {
            // timestamp is not set (or readable): everything is considered new
            return null;
        }

        return $lastRefresh > dateToUnix($timestamp);
    }

    /**
     * Returns last refresh timestamp
     *
     * @return int|null Unix-timestamp or null if last refresh isn't set yet.
     */
    abstract protected function _get(): ?int;

    /**
     * {@inheritDoc}
     */
    public function set(): void
    {
        $timestamp = new \DateTimeImmutable();

        $this->_set($timestamp);

        $this->_CurrentUser->set('last_refresh', $timestamp);
        // All postings indiviually marked as read are now older than the
        // last-refresh timestamp and can be removed.
        $this->_CurrentUser->getReadPostings()->delete();
    }

    /**
     * Set timestamp implementation
     *
     * @param \DateTimeImmutable $timestamp timestamp to set
     * @return void
     */
    abstract protected function _set(\DateTimeImmutable $timestamp): void;
}
