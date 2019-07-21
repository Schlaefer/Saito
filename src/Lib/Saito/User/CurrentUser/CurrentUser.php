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

use Bookmarks\Lib\Bookmarks;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\LastRefresh\LastRefreshInterface;
use Saito\User\ReadPostings\ReadPostingsInterface;
use Saito\User\SaitoUser;

/**
 * Implements the current user visiting the forum
 */
class CurrentUser extends SaitoUser implements CurrentUserInterface
{
    /**
     * Bookmarks manager
     *
     * @var Bookmarks
     */
    private $bookmarks = null;

    /**
     * Manages the last refresh/mark entries as read for the current user
     *
     * @var LastRefreshInterface
     */
    private $lastRefresh = null;

    /**
     * @var ReadPostingsInterface
     */
    private $readPostings;

    /**
     * Stores if a user is logged in. Stored individually for performance.
     *
     * @var bool
     */
    protected $isLoggedIn = false;

    /**
     * {@inheritDoc}
     */
    public function setSettings(array $settings): void
    {
        parent::setSettings($settings);

        $this->isLoggedIn = !empty($settings['id']);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $setting, $value)
    {
        if ($setting === 'id') {
            // @td @sm Class should probably immutable anyway. #Can-O-Worms
            $this->isLoggedIn = !empty($value);
            throw new \RuntimeException(
                'You are not allowed to change the user-ID.',
                1563729957
            );
        }

        parent::set($setting, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function setLastRefresh(LastRefreshInterface $lastRefresh): CurrentUserInterface
    {
        $this->lastRefresh = $lastRefresh;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastRefresh(): LastRefreshInterface
    {
        if ($this->lastRefresh === null) {
            throw new \RuntimeException(
                'CurrentUser has no LastRefresh. Set it before you get it.',
                1563704131
            );
        }

        return $this->lastRefresh;
    }

    /**
     * {@inheritDoc}
     */
    public function setReadPostings(ReadPostingsInterface $readPostings): CurrentUserInterface
    {
        $this->readPostings = $readPostings;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReadPostings(): ReadPostingsInterface
    {
        if ($this->readPostings === null) {
            throw new \RuntimeException(
                'CurrentUser has no ReadPostings. Set it before you get it.',
                1563704132
            );
        }

        return $this->readPostings;
    }

    /**
     * {@inheritDoc}
     */
    public function hasBookmarked($postingId)
    {
        if ($this->bookmarks === null) {
            $this->bookmarks = new Bookmarks($this);
        }

        return $this->bookmarks->isBookmarked($postingId);
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

    /**
     * {@inheritDoc}
     *
     * Time sensitive, might be called a few 100x on entries/index
     */
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }
}
