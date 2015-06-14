<?php

namespace Saito\User\ReadPostings;

/**
 * Nothing can be set read
 *
 * used as dummy for bots and test cases
 */
class ReadPostingsDummy extends ReadPostingsAbstract
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
    public function set($entries)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isRead($posting, $key = 'time')
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function _get()
    {
        return [];
    }
}
