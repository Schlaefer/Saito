<?php

namespace Saito\User\LastRefresh;

use App\Controller\Component\CurrentUserComponent;

/**
 * handles last refresh time for the current user
 */
abstract class LastRefreshAbstract
{

    /**
     * @var CurrentUserComponent
     */
    protected $_CurrentUser;

    /**
     * @var int unix timestamp
     */
    protected $_timestamp = null;

    /**
     * Constructor
     *
     * @param CurrentUserComponent $CurrentUser current-user
     */
    public function __construct(CurrentuserComponent $CurrentUser)
    {
        $this->_CurrentUser = $CurrentUser;
    }

    /**
     * is last refresh newer than $timestamp
     *
     * @param mixed $timestamp int unix-timestamp or date as string
     * @return mixed bool or null if not determinable
     */
    public function isNewerThan($timestamp)
    {
        $lastRefresh = $this->_get();
        // timestamp is not set (or readable): everything is considered new
        if ($lastRefresh === false) {
            return null;
        }

        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        } elseif (is_object($timestamp)) {
            $timestamp = $timestamp->toUnixString($timestamp);
        }

        return $lastRefresh > $timestamp;
    }

    /**
     * returns last refresh timestamp
     *
     * @return mixed int if unix timestamp or bool false if uninitialized
     */
    abstract protected function _get();

    /**
     * Set timestamp.
     *
     * @param mixed $timestamp null|'now'|<`Y-m-d H:i:s` timestamp>
     * @return void
     */
    public function set($timestamp = null)
    {
        // all postings individually marked as read should be removed because they
        // are older than the new last-refresh timestamp
        $this->_CurrentUser->ReadEntries->delete();

        $this->_timestamp = $this->_parseTimestamp($timestamp);
        $this->_set();
    }

    /**
     * Set timestamp implementation
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected abstract function _set();
    // @codingStandardsIgnoreEnd

    /**
     * Parse timestamp
     *
     * @param mixed $timestamp timestamp
     * @return bool|string
     */
    protected function _parseTimestamp($timestamp)
    {
        if ($timestamp === 'now' || $timestamp === null) {
            $timestamp = date('Y-m-d H:i:s');
        }

        return $timestamp;
    }
}
