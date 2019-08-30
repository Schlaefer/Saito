<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib\Helper;

class Message
{

    protected $_message = '';

    /**
     * reset message
     *
     * @return void
     */
    public function reset()
    {
        $this->_message = '';
    }

    /**
     * Set message
     *
     * @param string $message message
     *
     * @return void
     */
    public function set($message)
    {
        $this->_message = $message;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function get()
    {
        return self::format($this->_message);
    }

    /**
     * Format
     *
     * @param string $message string
     *
     * @return string
     */
    public static function format($message)
    {
        return "<div class='richtext-imessage'>$message</div>";
    }
}
