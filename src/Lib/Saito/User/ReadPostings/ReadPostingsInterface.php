<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\ReadPostings;

/**
 * Handles read postings for the current users
 */
interface ReadPostingsInterface
{
    /**
     * sets entry/entries as read for the current user
     *
     * @param array $postings single ['Entry' => []] or multiple [0 =>
     *     ['Entry' => …]
     * @return void
     */
    public function set($postings);

    /**
     * checks if entry is read by the current user
     *
     * if timestamp is provided it is checked against user's last refresh
     * time
     *
     * @param int $id posting-ID
     * @param mixed $timestamp unix timestamp or timestamp string
     * @return bool
     */
    public function isRead($id, $timestamp = null);

    /**
     * delete all read entries for the current user
     *
     * @return void
     */
    public function delete();
}
