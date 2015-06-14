<?php

/**
 * Sets variable to value if it's undefined
 *
 * @param string $variable variable
 * @param mixed $value value
 * @return void
 */
function SDV(&$variable, $value)
{
    if (!isset($variable)) {
        $variable = $value;
    }
}

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
