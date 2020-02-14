<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Admin\Lib;

/**
 * @bogus the ability to see logs isn't in Saito 5 anymore; see also AdminHelper::formatCakeLog
 */
class CakeLogEntry
{
    /**
     * Time
     *
     * @var string
     */
    protected $_time;

    /**
     * Time
     *
     * @var string
     */
    protected $_type;

    /**
     * Message
     *
     * @var string
     */
    protected $_message;

    /**
     * Detail
     *
     * @var string
     */
    protected $_detail;

    /**
     * Constructor
     *
     * @param string $text log entry text
     */
    public function __construct($text)
    {
        $lines = explode("\n", trim($text));
        $_firstLine = array_shift($lines);
        preg_match(
            '/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.*?): (.*)/',
            $_firstLine,
            $matches
        );
        $this->_time = $matches[1];
        $this->_type = $matches[2];
        $this->_message = trim($matches[3]);
        if (empty($this->_message)) {
            $this->_message = array_shift($lines);
        }
        $this->_detail = implode($lines, '<br>');
    }

    /**
     * Gets log entry time
     *
     * @return string
     */
    public function time()
    {
        return $this->_time;
    }

    /**
     * Gets log entry type
     *
     * @return string
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * Gets log entry message
     *
     * @return string
     */
    public function message()
    {
        return $this->_message;
    }

    /**
     * Gets log entry details
     *
     * @return string
     */
    public function details()
    {
        return $this->_detail;
    }
}
