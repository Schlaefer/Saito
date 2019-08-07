<?php

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test\User\Cookie;

use Cake\Chronos\Chronos;
use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Saito\Test\SaitoTestCase;
use Saito\User\Cookie\CurrentUserCookie;

/**
 * Class CategoriesTest
 *
 * @group Saito\Test\User\CategoriesTest
 */
class CurrentUserCookieTest extends SaitoTestCase
{
    private $cookieKey = 'test';

    private $controller;

    public function testRead()
    {
        $future = time() + 60;
        $cookie = $this->createCookie(['id' => 5, 'refreshAfter' => $future]);

        $result = $cookie->read();

        $this->assertEquals(5, $result['id']);

        $result = $this->getCookie();

        // refresh is not triggered
        $this->assertNull($result);
    }

    public function testWrite()
    {
        $cookie = $this->createCookie();

        $cookie->write(3);
        $result = $this->getCookie();

        $this->assertWithinRange(Chronos::parse('+30 days')->getTimestamp(), $result['expire'], 2);
        $this->assertArrayHasKey('refreshAfter', $result['value']);
        $this->assertWithinRange(Chronos::parse('+23 days')->getTimestamp(), $result['value']['refreshAfter'], 2);
    }

    public function testRefresh()
    {
        $past = time() - 60;
        $cookie = $this->createCookie(['id' => 5, 'refreshAfter' => $past]);

        $cookie->read();
        $result = $this->getCookie();

        $this->assertArrayHasKey('refreshAfter', $result['value']);
        $this->assertWithinRange(Chronos::parse('+23 days')->getTimestamp(), $result['value']['refreshAfter'], 2);
    }

    public function testRefreshOnPreviousVersionCookie()
    {
        $cookie = $this->createCookie(['id' => 5]);

        $cookie->read();
        $result = $this->getCookie();

        $this->assertArrayHasKey('refreshAfter', $result['value']);
    }

    public function testDeleteUnreadableCookie()
    {
        $cookie = $this->createCookie(['foerkd']);

        $result = $cookie->read();

        $this->assertNull($result);

        $result = $this->getCookie();
        $this->assertEquals($result['expire'], '1');
    }

    public function tearDown()
    {
        unset($this->cookieKey, $this->controller);

        parent::tearDown();
    }

    private function createCookie(?array $data = null): CurrentUserCookie
    {
        $request = (new ServerRequest());
        if ($data) {
            $request = $request->withCookieParams([$this->cookieKey => $data]);
        }
        $response = new Response();
        $this->controller = $this->getMockBuilder(Controller::class)
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();

        return new CurrentUserCookie($this->controller, $this->cookieKey);
    }

    private function getCookie()
    {
        $data = $this->controller->getResponse()->getCookie($this->cookieKey);

        if (empty($data)) {
            return $data;
        }

        $data['value'] = json_decode($data['value'], true);

        return $data;
    }
}
