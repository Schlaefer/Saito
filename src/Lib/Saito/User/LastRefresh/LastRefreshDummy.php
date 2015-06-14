<?php

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
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function _get()
    {
        return strtotime('+1 week');
    }

    /**
     * {@inheritDoc}
     */
    public function set($timestamp = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function _set()
    {
    }
}
