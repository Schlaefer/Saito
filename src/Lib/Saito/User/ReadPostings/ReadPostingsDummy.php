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
