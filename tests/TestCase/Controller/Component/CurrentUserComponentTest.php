<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\CurrentUserComponent;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Saito\Test\IntegrationTestCase;

class CurrentUserComponentTest extends IntegrationTestCase
{
    /**
     * {@inheritDoc}
     */
    public $fixtures = [
        'app.user',
        'app.useronline',
    ];

    /**
     * @var Component
     */
    public $component = null;

    /**
     * @var Controller
     */
    public $controller = null;

    public function setUp()
    {
        parent::setUp();
        // Setup our component and fake test controller
        $request = new ServerRequest();
        $response = new Response();
        $this->controller = $this->getMockBuilder(Controller::class)
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        $this->controller->loadComponent('Auth');
        $registry = new ComponentRegistry($this->controller);
        $this->component = new CurrentUserComponent($registry);
        // $event = new Event('Controller.startup', $this->controller);
        // $this->component->startup($event);
    }

    public function tearDown()
    {
        parent::tearDown();
        // Clean up after we're done
        unset($this->component, $this->controller);
    }

    /**
     * Cookie should not be set on anonoymous user
     *
     * @return void
     */
    public function testSetJwtCookieNoCookieSet()
    {
        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-jwt');
        $this->assertNull($cookie);
    }

    /**
     * Set cookie on logged-in user 1
     *
     * @return void
     */
    public function testSetJwtCookieLoggedInSetCookieSet()
    {
        $user = $this->_loginUser(1);
        $this->component->setSettings($user);

        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-jwt');
        $this->assertNotEmpty($cookie);
        $this->assertSame('Saito-jwt', $cookie['name']);
        $this->assertFalse($cookie['httpOnly']);
    }

    /**
     * Delete cookie if set and user is not logged-in
     *
     * @return void
     */
    public function testSetJwtCookieDeleteCookieIfNotLoggedIn()
    {
        $request = $this->controller->getRequest();
        $request = $request->withCookieParams(['Saito-jwt' => 'foo']);
        $this->controller->setRequest($request);

        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-jwt');
        $this->assertNotEmpty($cookie);
        $this->assertSame('Saito-jwt', $cookie['name']);
        $this->assertSame('1', $cookie['expire']);
    }

    /**
     * Replace token if token doesn't belong to current user
     *
     * @return void
     */
    public function testSetJwtCookieCheckUserAndReplace()
    {
        $newUser = 1;
        $user = $this->_loginUser($newUser);
        $this->component->setSettings($user);

        $jwtKey = Configure::read('Security.cookieSalt');

        $oldUser = 2;
        $jwtPayload = ['sub' => $oldUser];
        $jwtToken = \Firebase\JWT\JWT::encode($jwtPayload, $jwtKey);
        $request = $this->controller->getRequest();
        $request = $request->withCookieParams(['Saito-jwt' => $jwtToken]);
        $this->controller->setRequest($request);

        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-jwt');
        $this->assertNotEmpty($cookie);
        $this->assertSame('Saito-jwt', $cookie['name']);

        $payload = \Firebase\JWT\JWT::decode($cookie['value'], $jwtKey, ['HS256']);
        $this->assertEquals(1, $payload->sub);
    }
}
