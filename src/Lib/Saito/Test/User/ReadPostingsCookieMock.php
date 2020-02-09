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

use Saito\User\ReadPostings\ReadPostingsCookie;

class ReadPostingsCookieMock extends ReadPostingsCookie
{
    /**
     * @param mixed $maxPostings
     */
    public function setMaxPostings($maxPostings)
    {
        $this->maxPostings = $maxPostings;
    }

    public function setLastRefresh($LR)
    {
        $this->LastRefresh = $LR;
    }

    public function __get($property)
    {
        if ($property === 'Cookie') {
            return $this->storage;
        }
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
    }

    public function __call($method, $arguments)
    {
        if (is_callable([$this, $method])) {
            return call_user_func_array([$this, $method], $arguments);
        }
    }
}
