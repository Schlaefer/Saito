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
use Cake\Http\Client\Request;
use GuzzleHttp\Psr7\Uri;
use Zend\Diactoros\Request as ZendRequest;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class ApplicationTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
    ];

    private const API_ROOT = 'api/v2';

    /** @var Application */
    private $application;

    public function setUp()
    {
        parent::setUp();

        $this->application = new Application(__DIR__);
    }

    public function teardDown()
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
            $request = new ServerRequest([], [], $url);
            $response = new Response();

            $provider = $this->application->getAuthenticationService($request, $response);

            $authenticator = $provider->authenticators()->get('Jwt');
            $this->assertNotEmpty($authenticator);

            $authenticator = $provider->authenticators()->get('Session');
            $this->assertEmpty($authenticator);
            $authenticator = $provider->authenticators()->get('Cookie');
            $this->assertEmpty($authenticator);
        }
    }

    public function testGetAuthenticationServiceApp()
    {
        $urls = [ '/', '/foo', '/foo/', ];

        foreach ($urls as $url) {
            $request = new ServerRequest([], [], $url);
            $response = new Response();

            $provider = $this->application->getAuthenticationService($request, $response);
            $authenticator = $provider->authenticators()->get('Session');

            $this->assertNotEmpty($authenticator);
        }
    }
}
