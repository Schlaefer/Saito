<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

/**
 * returns date in SQL friendly format
 *
 * @param int $timestamp timestamp
 * @return string date string
 */
function bDate($timestamp = null): string
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    return date('Y-m-d H:i:s', $timestamp);
}

/**
 * Converts a timestamp-entity into unix-timestamp
 *
 * @param int|string|\DateTimeInterface $timestamp to convert
 * @return int unix timestamp
 */
function dateToUnix($timestamp): int
{
    if (is_int($timestamp)) {
        return $timestamp;
    }

    if (is_object($timestamp)) {
        return $timestamp->getTimestamp();
    }

    $unix = strtotime($timestamp);
    if ($unix < 0 || $unix === false) {
        throw new \RuntimeException(
            "Can't convert timestamp $timestamp to unix-timestamp.",
            1524230476
        );
    }

    return $unix;
}

/**
 * timestamp to iso
 *
 * @param int|string|\DateTimeInterface $timestamp to convert
 * @return string
 */
function dateToIso($timestamp): string
{
    return date('c', dateToUnix($timestamp));
}
