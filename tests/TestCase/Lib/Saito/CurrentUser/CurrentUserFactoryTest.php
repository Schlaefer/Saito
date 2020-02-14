<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User;

use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

class CurrentUserFactoryTest extends SaitoTestCase
{
    /**
     * @var Controller
     */
    private $controller;

    public function testCurrentUserFactoryVisitorIsRoleAnon()
    {
        $user = CurrentUserFactory::createVisitor($this->controller);
        $this->assertEquals('anon', $user->getRole());
    }

    public function setUp(): void
    {
        parent::setUp();

        $request = new ServerRequest();
        $response = new Response();
        $this->controller = new Controller($request, $response);
    }

    public function tearDown(): void
    {
        unset($this->controller);

        parent::tearDown();
    }
}
