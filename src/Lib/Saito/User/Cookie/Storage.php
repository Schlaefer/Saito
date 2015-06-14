<?php

namespace Saito\User\Cookie;

use Cake\Controller\Component\CookieComponent;

/**
 * Class Storage
 *
 * base cookie class for Saito with some default values
 *
 * @package Saito\User\Cookie
 */
class Storage
{

    protected $_Cookie;

    protected $_defaults = [
        'encryption' => 'aes',
        'expires' => '+1 month',
        'httpOnly' => true
    ];

    protected $_key;

    /**
     * Constructor
     *
     * @param CookieComponent $Cookie component
     * @param string $key cookie-key
     */
    public function __construct(CookieComponent $Cookie, $key)
    {
        $this->_Cookie = $Cookie;
        $this->_key = $key;

        return $this;
    }

    /**
     * Read cookie.
     *
     * @return string
     */
    public function read()
    {
        return $this->_Cookie->read($this->_key);
    }

    /**
     * Write cookie
     *
     * @param mixed $data data
     * @return void
     */
    public function write($data)
    {
        $this->_Cookie->write($this->_key, $data);
    }

    /**
     * Delete
     *
     * @return void
     */
    public function delete()
    {
        $this->_Cookie->delete($this->_key);
    }

    /**
     * Set config.
     *
     * @param array $options options
     * @return $this
     */
    public function setConfig($options)
    {
        $this->_Cookie->configKey($this->_key, $options + $this->_defaults);

        return $this;
    }
}
