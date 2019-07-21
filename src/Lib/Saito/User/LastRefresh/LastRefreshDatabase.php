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

use App\Model\Table\UsersTable;
use DateTimeInterface;
use Saito\RememberTrait;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * handles last refresh time for current user via database
 *
 * used for logged-in users
 */
class LastRefreshDatabase extends LastRefreshAbstract
{
    use RememberTrait;

    /**
     * @var UsersTable
     */
    protected $storage;

    private $initialized = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(CurrentUserInterface $CurrentUser, UsersTable $storage)
    {
        parent::__construct($CurrentUser, $storage);
    }

    /**
     * {@inheritDoc}
     */
    protected function _get(): ?int
    {
        return $this->remember('timestamp', function () {
            // can't use ArrayIterator access because array_key_exists doesn't work
            // on ArrayIterator … Yeah for PHP!1!!
            $settings = $this->_CurrentUser->getSettings();
            if (!array_key_exists('last_refresh', $settings)) {
                throw new \Exception('last_refresh not set');
            } elseif ($settings['last_refresh'] === null) {
                // mar is not initialized
                return null;
            }

            return $this->_CurrentUser->get('last_refresh_unix');
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function _set(\DateTimeImmutable $timestamp): void
    {
        $this->persist($timestamp);
    }

    /**
     * Set temporary marker
     *
     * @return void
     */
    public function setMarker()
    {
        $this->persist();
    }

    /**
     * Persist to strorage
     *
     * @param DateTimeInterface $timestamp datetime string for last_refresh
     * @return void
     */
    protected function persist(DateTimeInterface $timestamp = null): void
    {
        $this->_storage->setLastRefresh(
            $this->_CurrentUser->getId(),
            $timestamp
        );
    }
}
