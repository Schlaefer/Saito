<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use App\Application;
use Cake\Http\Client\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;

class ApplicationTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
    ];

    private const API_ROOT = 'api/v2';

    /**
     * @var Application
     */
    private $application;

    public function setUp(): void
    {
        parent::setUp();

        $this->application = new Application(CONFIG);
        $builder = Router::createRouteBuilder('/');
        $this->application->routes($builder);
    }

    public function teardDown(): void
    {
        unset($this->application);
        parent::tearDown();
    }

    public function testGetAuthenticationServiceJwt()
    {
        $urls = [
            '/foo/' . self::API_ROOT . '/foo',
            '/' . self::API_ROOT,
            '/' . self::API_ROOT . '/',
        ];

        foreach ($urls as $url) {
            $request = new ServerRequest(['url' => $url]);
            $response = new Response();

            $provider = $this->application->getAuthenticationService($request, $response);

            $this->assertTrue($provider->authenticators()->has('Jwt'));
            $this->assertFalse($provider->authenticators()->has('Session'));
            $this->assertFalse($provider->authenticators()->has('Cookie'));
        }
    }

    public function testGetAuthenticationServiceApp()
    {
        $urls = [ '/', '/foo', '/foo/', ];

        foreach ($urls as $url) {
            $request = new ServerRequest([], [], $url);
            $response = new Response();

            $provider = $this->application->getAuthenticationService($request, $response);
            $this->assertTrue($provider->authenticators()->has('Session'));
        }
    }
}
