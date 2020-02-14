<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Lib\Saito\Test\Posting;

use Saito\Posting\Posting;

class UserPostingTraitClassMock extends Posting
{
    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Set
     * @param string $key key
     * @param mixed|null $val value
     * @return void
     */
    public function set($key, $val = null)
    {
        if ($val === null) {
            $this->_rawData = $key;

            return;
        }
        $this->_rawData[$key] = $val;
    }
}
