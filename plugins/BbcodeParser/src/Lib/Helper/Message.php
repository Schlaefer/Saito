<?php

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
