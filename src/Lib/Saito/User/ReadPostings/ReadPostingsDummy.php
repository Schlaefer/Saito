<?php

namespace Saito\User\ReadPostings;

use Saito\User\ReadPostings\ReadPostingsInterface;

/**
 * Nothing can be set read
 *
 * used as dummy for bots and test cases
 */
class ReadPostingsDummy implements ReadPostingsInterface
{
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
}
