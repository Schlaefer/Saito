<?php

/**
 * returns date in SQL friendly format
 *
 * @param int $timestamp timestamp
 * @return string
 */
function bDate($timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    return date('Y-m-d H:i:s', $timestamp);
}
