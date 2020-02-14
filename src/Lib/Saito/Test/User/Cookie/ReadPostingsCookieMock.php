<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Lib\Saito\Test\User\Cookie;

use Saito\User\LastRefresh\LastRefreshInterface;
use Saito\User\ReadPostings\ReadPostingsCookie;

/**
 * Mock for ReadPostingsCookie
 */
class ReadPostingsCookieMock extends ReadPostingsCookie
{
    /**
     * Set max $maxPostings
     *
     * @param int $maxPostings max postings
     * @return void
     */
    public function setMaxPostings(int $maxPostings): void
    {
        $this->maxPostings = $maxPostings;
    }

    /**
     * Set last refresh
     *
     * @param \Saito\User\LastRefresh\LastRefreshInterface $LR LastRefresh
     * @return void
     */
    public function setLastRefresh(LastRefreshInterface $LR): void
    {
        $this->LastRefresh = $LR;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($property)
    {
        if ($property === 'Cookie') {
            return $this->storage;
        }
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        if (is_callable([$this, $method])) {
            return call_user_func_array([$this, $method], $arguments);
        }
    }
}
