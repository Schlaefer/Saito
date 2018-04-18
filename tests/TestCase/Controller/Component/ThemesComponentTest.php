<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\ThemesComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Network\Request;
use Saito\Test\SaitoTestCase;
use Saito\User\SaitoUser;

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
        $this->component->settings($config);

        $user = new SaitoUser();
        $this->controller->CurrentUser = $user;

        $this->assertNotEquals('foo', $this->controller->viewBuilder()->getTheme());
        $this->component->set();
        $this->assertEquals('foo', $this->controller->viewBuilder()->getTheme());
    }

    public function testSetCustomThemeAndDefaultSet()
    {
        $config = ['default' => 'foo', 'available' => ['bar']];
        $this->component->settings($config);

        $user = new SaitoUser(['user_theme' => 'bar']);
        $this->controller->CurrentUser = $user;

        // test custom theme applied
        $this->component->set();
        $this->assertEquals('bar', $this->controller->viewBuilder()->getTheme());

        // test default set
        $this->component->setDefault();
        $this->assertEquals('foo', $this->controller->viewBuilder()->getTheme());
    }

    public function testCustomThemeNotAvailable()
    {
        $config = ['default' => 'foo'];
        $this->component->settings($config);

        $user = new SaitoUser(['user_theme' => 'bar']);
        $this->controller->CurrentUser = $user;

        $this->component->set();
        $this->assertEquals('foo', $this->controller->viewBuilder()->getTheme());
    }
}
