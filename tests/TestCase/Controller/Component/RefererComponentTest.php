<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\RefererComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Network\Response;
use Cake\Network\Session;
use Saito\Test\SaitoTestCase;
use Saito\User\SaitoUser;

class RefererComponentTest extends SaitoTestCase
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
        $request = new ServerRequest('/users/view/5');
        $response = new Response();
        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        $registry = new ComponentRegistry($this->controller);
        $this->component = new RefererComponent($registry);
    }

    public function tearDown()
    {
        parent::tearDown();
        // Clean up after we're done
        unset($this->component, $this->controller);
    }

    public function testLocalReferer()
    {
        $baseUrl = 'http://localhost';
        Configure::write('App.fullBaseUrl', $baseUrl);

        $request = new ServerRequest('/users/view/5');
        $webroot = $this->component->request->webroot;
        $this->component->request = $request;

        $request->env('HTTP_REFERER', $baseUrl . $webroot . '/');
        $event = new Event('Controller.beforeFilter', $this->controller);
        $this->component->beforeFilter($event);
        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);
        $result = $this->component->getController()->viewVars['referer'];
        $this->assertEquals(['controller' => 'entries', 'action' => 'index'], $result);
        $this->assertTrue($this->component->wasController('entries'));
        $this->assertFalse($this->component->wasController('users'));
        $this->assertTrue($this->component->wasAction('index'));
        $this->assertFalse($this->component->wasAction('view'));

        $request->env('HTTP_REFERER', $baseUrl . $webroot . '/entries/view');
        $event = new Event('Controller.beforeFilter', $this->controller);
        $this->component->beforeFilter($event);
        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);
        $result = $this->component->getController()->viewVars['referer'];
        $this->assertEquals(['controller' => 'entries', 'action' => 'view'], $result);
        $this->assertTrue($this->component->wasController('entries'));
        $this->assertFalse($this->component->wasController('users'));
        $this->assertTrue($this->component->wasAction('view'));
        $this->assertFalse($this->component->wasAction('index'));

        $request->env('HTTP_REFERER', $baseUrl . $webroot . '/some/');
        $event = new Event('Controller.beforeFilter', $this->controller);
        $this->component->beforeFilter($event);
        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);
        $result = $this->component->getController()->viewVars['referer'];
        $this->assertEquals(['controller' => 'some', 'action' => 'index'], $result);
        $this->assertTrue($this->component->wasController('some'));
        $this->assertFalse($this->component->wasController('entries'));
        $this->assertTrue($this->component->wasAction('index'));

        //* external referer
        $request->env('HTTP_REFERER', 'http://heise.de/foobar/baz.html');
        $event = new Event('Controller.beforeFilter', $this->controller);
        $this->component->beforeFilter($event);
        $event = new Event('Controller.beforeRender', $this->controller);
        $this->component->beforeRender($event);
        $result = $this->component->getController()->viewVars['referer'];
        $this->assertEquals([], $result);
        $this->assertFalse($this->component->wasController('entries'));
        $this->assertFalse($this->component->wasAction('index'));
    }
}
