<?php

namespace Saito\User\Cookie;

use Cake\Chronos\Chronos;
use Cake\Controller\Controller;
use Cake\Http\Cookie\Cookie;
use Cake\Routing\Router;

/**
 * Class Storage
 *
 * base cookie class for Saito with some default values
 *
 * @package Saito\User\Cookie
 */
class Storage
{
    /**
     * Cookie is HTTP only
     */
    private const HTTP = true;

    /**
     * Expire time for cookies
     */
    private const EXPIRE = '+1 month';

    /**
     * Controller
     *
     * @var Controller
     */
    protected $_Controller;

    protected $_key;

    /**
     * Constructor
     *
     * @param Controller $controller Controller
     * @param string $key cookie-key
     */
    public function __construct(Controller $controller, $key)
    {
        $this->_Controller = $controller;
        $this->_key = $key;
    }

    /**
     * Read cookie.
     *
     * @return string
     */
    public function read()
    {
        // raw string value of cookie
        $raw = $this->_Controller->request->getCookie($this->_key);
        if ($raw === null) {
            return null;
        }
        // Cake 3 encodes complex cookie-data (json), this decodes again
        $value = (new Cookie('dummy'))
            ->withValue($raw)
            ->read();

        return $value;
    }

    /**
     * Write cookie
     *
     * @param mixed $data data
     * @return void
     */
    public function write($data)
    {
        $cookie = $this->createCookie()
            ->withValue($data);
        $this->_Controller->response = $this->_Controller->response->withCookie($cookie);
    }

    /**
     * Delete
     *
     * @return void
     */
    public function delete()
    {
        $cookie = $this->createCookie();
        $this->_Controller->response = $this->_Controller->response->withExpiredCookie($cookie);
    }

    /**
     * Creates a new CakePHP cookie instance with default values set
     *
     * @return Cookie
     */
    private function createCookie(): Cookie
    {
        $cookie = (new Cookie($this->_key))
            ->withPath(Router::url('/', false))
            ->withHttpOnly(self::HTTP)
            ->withExpiry(new Chronos(self::EXPIRE));

        return $cookie;
    }
}
