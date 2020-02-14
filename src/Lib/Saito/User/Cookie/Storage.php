<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Cookie;

use Cake\Chronos\Chronos;
use Cake\Controller\Controller;
use Cake\Core\InstanceConfigTrait;
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
    use InstanceConfigTrait;

    /**
     * default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'expire' => '+1 month',
        'http' => true,
    ];

    /**
     * Controller
     *
     * @var \Cake\Controller\Controller
     */
    protected $Controller;

    protected $key;

    /**
     * Constructor
     *
     * @param \Cake\Controller\Controller $controller Controller
     * @param string $key cookie-key
     * @param array $config additional options
     */
    public function __construct(Controller $controller, ?string $key = null, array $config = [])
    {
        if (empty($key)) {
            throw new \LogicException('Cookie must not be empty.', 1525764689);
        }
        $this->Controller = $controller;
        $this->key = $key;
        $this->setConfig($config);
    }

    /**
     * Read cookie.
     *
     * @return null|mixed null if cookie not set, otherwise it's contents
     */
    public function read()
    {
        // raw string value of cookie
        $raw = $this->Controller->getRequest()->getCookie($this->key);
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
    public function write($data): void
    {
        $cookie = $this->createCookie()
            ->withValue($data);
        $this->Controller->setResponse($this->Controller->getResponse()->withCookie($cookie));
    }

    /**
     * Delete
     *
     * @return void
     */
    public function delete(): void
    {
        $cookie = $this->createCookie();
        $this->Controller->setResponse($this->Controller->getResponse()->withExpiredCookie($cookie));
    }

    /**
     * Creates a new CakePHP cookie instance with default values set
     *
     * @return \Cake\Http\Cookie\Cookie
     */
    private function createCookie(): Cookie
    {
        $cookie = (new Cookie($this->key))
            ->withPath(Router::url('/', false))
            ->withHttpOnly($this->getConfig('http'))
            ->withExpiry(new Chronos($this->getConfig('expire')));

        return $cookie;
    }
}
