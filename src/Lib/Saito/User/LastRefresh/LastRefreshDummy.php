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

/**
 * everything is always read
 *
 * used as dummy for bots and test cases
 */
class LastRefreshDummy extends LastRefreshAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function _get(): ?int
    {
        return strtotime('+1 week');
    }

    /**
     * {@inheritDoc}
     */
    public function set(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function _set(\DateTimeImmutable $timestamp): void
    {
    }
}
