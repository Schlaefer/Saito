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
 * handles last refresh time for the current user
 */
interface LastRefreshInterface
{
    /**
     * Checks if last refresh newer than $timestamp.
     *
     * Performance sensitive: every posting on /entries/index may be
     * tested if new for user.
     *
     * @param string|\DateTimeInterface $timestamp int unix-timestamp or date as string
     * @return bool|null if not determinable
     */
    public function isNewerThan($timestamp): ?bool;

    /**
     * Mark last refresh as "now"
     *
     * @return void
     */
    public function set(): void;

    /**
     * Set temporary marker
     *
     * @return void
     */
    public function setMarker(): void;
}
