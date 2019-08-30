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

use Saito\RememberTrait;
use Saito\User\Cookie\Storage;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * handles last refresh time for current user via cookie
 *
 * used for non logged-in users
 */
class LastRefreshCookie extends LastRefreshAbstract
{
    use RememberTrait;

    protected $_Cookie;

    /**
     * @var Storage
     */
    protected $_storage;

    /**
     * {@inheritDoc}
     */
    public function __construct(CurrentUserInterface $CurrentUser, Storage $storage)
    {
        parent::__construct($CurrentUser, $storage);
    }

    /**
     * {@inheritDoc}
     */
    protected function _get(): ?int
    {
        return $this->remember('timestamp', function () {
            $timestamp = $this->_storage->read();

            return empty($timestamp) ? null : (int)$timestamp;
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function _set(\DateTimeImmutable $timestamp): void
    {
        $this->_storage->write($timestamp->getTimestamp());
    }
}
