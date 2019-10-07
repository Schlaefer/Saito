<?php

/**
 * Saito - The Threaded Web Forum
 * * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\ThemesComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Network\Request;
use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

/**
 * ThemesComponent Test Case
 *
 */
class ThemesComponentTest extends SaitoTestCase
{

    /**
     * @var ThemesComponent
     */
    public $component;

    /**
     * @var Controller
     */
    public $controller;

    public function setUp()
    {
        parent::setUp();
        // Setup our component and fake test controller
        $request = new Request();
        $response = new Response();
        $this->controller = new Controller($request, $response);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new ThemesComponent($registry);
    }

    public function tearDown()
    {
        unset($this->component, $this->controller);
        parent::tearDown();
    }

    public function testApplyDefaultTheme()
    {
        $config = ['default' => 'foo'];
        $this->component->setConfig($config);

        $user = CurrentUserFactory::createDummy();

        $this->assertNotEquals('foo', $this->controller->viewBuilder()->getTheme());
        $this->component->set($user);
        $this->assertEquals('foo', $this->controller->viewBuilder()->getTheme());
    }

    public function testSetCustomThemeAndDefaultSet()
    {
        $config = ['default' => 'foo', 'available' => ['bar']];
        $this->component->setConfig($config);

        $user = CurrentUserFactory::createDummy(['id' => 1, 'user_theme' => 'bar']);

        // test custom theme applied
        $this->component->set($user);
        $this->assertEquals('bar', $this->controller->viewBuilder()->getTheme());

        // test default set
        $this->component->setDefault();
        $this->assertEquals('foo', $this->controller->viewBuilder()->getTheme());
    }

    public function testCustomThemeNotAvailable()
    {
        $config = ['default' => 'foo'];
        $this->component->setConfig($config);

        $user = CurrentUserFactory::createDummy(['id' => '1', 'user_theme' => 'bar']);

        $this->component->set($user);
        $this->assertEquals('foo', $this->controller->viewBuilder()->getTheme());
    }
}
