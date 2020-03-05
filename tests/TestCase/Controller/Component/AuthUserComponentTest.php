<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Controller\Component;

use App\Auth\AuthenticationServiceFactory;
use App\Controller\Component\AuthUserComponent;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\PasswordHasher\PasswordHasherFactory;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ServerRequestInterface;
use Saito\Test\IntegrationTestCase;
use Saito\User\CurrentUser\CurrentUserInterface;

class AuthUserComponentTest extends IntegrationTestCase
{
    /**
     * {@inheritDoc}
     */
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.User',
        'app.UserIgnore',
        'app.UserOnline',
    ];

    /**
     * @var AuthUserComponent
     */
    public $component = null;

    /**
     * @var Controller
     */
    public $controller = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadRoutes();
        $this->_setup();
    }

    public function tearDown(): void
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
        $this->component->getUser()->setSettings($user);

        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-JWT');
        $this->assertNotEmpty($cookie);
        $this->assertSame('Saito-JWT', $cookie['name']);
        $this->assertFalse($cookie['httponly']);
    }

    /**
     * Delete cookie if set and user is not logged-in
     *
     * @return void
     */
    public function testSetJwtCookieDeleteCookieIfNotLoggedIn()
    {
        $request = $this->controller->getRequest();
        $request = $request->withCookieParams(['Saito-JWT' => 'foo']);
        $this->controller->setRequest($request);

        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-JWT');
        $this->assertNotEmpty($cookie);
        $this->assertSame('Saito-JWT', $cookie['name']);
        $this->assertSame(1, $cookie['expires']);
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
        $this->component->getUser()->setSettings($user);

        $jwtKey = Configure::read('Security.cookieSalt');

        $oldUser = 2;
        $jwtPayload = ['sub' => $oldUser, 'exp' => time() + 10];
        $jwtToken = \Firebase\JWT\JWT::encode($jwtPayload, $jwtKey);
        $request = $this->controller->getRequest();
        $request = $request->withCookieParams(['Saito-JWT' => $jwtToken]);
        $this->controller->setRequest($request);

        $event = new Event('Controller.shutdown', $this->controller);
        $this->component->shutdown($event);

        $cookie = $this->controller->getResponse()->getCookie('Saito-JWT');
        $this->assertNotEmpty($cookie);
        $this->assertSame('Saito-JWT', $cookie['name']);

        $payload = \Firebase\JWT\JWT::decode($cookie['value'], $jwtKey, ['HS256']);
        $this->assertEquals(1, $payload->sub);
    }

    public function testLoginSuccessSession()
    {
        $request = ServerRequestFactory::fromGlobals();

        /** @var UserIgnoresTable $Ignores */
        $Ignores = TableRegistry::getTableLocator()->get('UserIgnores');
        $Ignores->ignore(3, 7);

        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['read'])
            ->getMock();
        $session->expects($this->at(0))
           ->method('read')
           ->with('Auth')
           ->will($this->returnValue([
               'username' => 'Ulysses',
           ]));

        $request = $request->withAttribute('session', $session);
        $this->_setup($request);

        $this->component->login();

        /// CurrentUser exists and is set
        $CU = $this->component->getUser();
        $this->assertInstanceOf(CurrentUserInterface::class, $CU);
        $this->assertSame($CU, $this->controller->CurrentUser);
        $this->assertEquals('Ulysses', $CU->get('username'));

        /// Check that ignores data is attached to CurrentUser
        $this->assertTrue($CU->ignores(7));
    }

    /**
     * Test that the authentication cookie is refreshed.
     *
     * @return void
     */
    public function testAuthenticationRefresh()
    {
        /// Setup the request for the authenticator
        $Users = TableRegistry::getTableLocator()->get('Users');
        $user = $Users->get(1);
        $hasher = PasswordHasherFactory::build(DefaultPasswordHasher::class);
        $username = $user->get('username');
        $hash = $hasher->hash($username . $user->get('password'));
        $cookieName = Configure::read('Security.cookieAuthName');
        $webroot = '/sub/';
        $request = (new ServerRequest([
            'cookies' => [$cookieName => json_encode([$username, $hash])],
            'webroot' => $webroot,
        ]));
        $this->_setup($request);

        /// Trigger refresh on cookie-login
        $this->component->login();

        /// Test that cookie is set
        $cookie = $this->controller->getResponse()->getCookie($cookieName);
        $this->assertNotEmpty($cookie);

        /// Test that cookie expiry is set
        $authProvider = $this->component->Authentication
            ->getAuthenticationService()
            ->authenticators()
            ->get('Cookie');
        $expire = $authProvider->getConfig('cookie.expire');
        $this->assertWithinRange($expire->getTimestamp(), (int)$cookie['expires'], 2);
        $this->assertEquals($webroot, $cookie['path']);
    }

    private function _setup(?ServerRequestInterface $request = null)
    {
        $request = $request ?: new ServerRequest();
        $response = new Response();

        $service = AuthenticationServiceFactory::buildApp();
        $result = $service->authenticate($request, $response);

        $request = $request->withAttribute('authentication', $service);
        // $request = $request->withAttribute('authenticationResult', $result['result']);

        $controller = new Controller($request, $response);

        $registry = new ComponentRegistry($controller);
        $component = new AuthUserComponent($registry);

        $this->component = $component;
        $this->controller = $controller;
    }
}
