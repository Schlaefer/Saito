<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\ThemesComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
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
        unset($this->component);
        parent::tearDown();
    }

    public function testApplyDefaultTheme()
    {
        $config = ['default' => 'foo'];
        $user = new SaitoUser();

        $this->assertNotEquals('foo', $this->controller->theme);
        $this->component->theme($config, $user);
        $this->assertEquals('foo', $this->controller->theme);
    }

    public function testSetCustomThemeAndDefaultSet()
    {
        $config = ['default' => 'foo', 'available' => ['bar']];
        $user = new SaitoUser(['user_theme' => 'bar']);

        // test custom theme applied
        $this->component->theme($config, $user);
        $this->assertEquals('bar', $this->controller->theme);

        // test default set
        $this->component->setDefault();
        $this->assertEquals('foo', $this->controller->theme);
    }

    public function testCustomThemeNotAvailable()
    {
        $config = ['default' => 'foo'];
        $user = new SaitoUser(['user_theme' => 'bar']);

        $this->component->theme($config, $user);
        $this->assertEquals('foo', $this->controller->theme);
    }
}
