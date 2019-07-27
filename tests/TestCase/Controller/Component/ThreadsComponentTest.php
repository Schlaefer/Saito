<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\ThreadsComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUser;

/**
 * Class ThemesComponentTest
 *
 * @package App\Test\TestCase\Controller\Component
 */
class ThreadsComponentTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User'
    ];

    /**
     * @var ThreadsComponent
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
        $this->component = new ThreadsComponent($registry);
    }

    public function tearDown()
    {
        unset($this->component, $this->controller);
        parent::tearDown();
    }

    public function testThreadIncrementView()
    {
        $tid = 4;

        $this->component->AuthUser = $this->getMockBuilder(AuthUserComponent::class)
            ->setMethods(['isBot'])
            ->getMock();
        $this->component->AuthUser->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        Registry::set('CU', (new CurrentUser([], $this->component->getController())));

        $Entries = TableRegistry::get('Entries');
        $posting = $Entries->get(4);

        $this->component->incrementViews($posting, 'thread');

        $Entries = TableRegistry::get('Entries');
        $result = $Entries->find()
            ->select('views')
            ->where(['tid' => $tid])
            ->toArray();
        $this->assertEquals(1, array_shift($result)->get('views'));
        $this->assertEquals(1, array_shift($result)->get('views'));
    }

    public function testThreadIncrementViewOmitUser()
    {
        $tid = 4;
        $this->component->AuthUser = $this->getMockBuilder(AuthUserComponent::class)
            ->setMethods(['isBot'])
            ->getMock();
        $this->component->AuthUser->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        Registry::set('CU', (new CurrentUser(['id' => 3], $this->component->getController())));

        $Entries = TableRegistry::get('Entries');
        $posting = $Entries->get(4);

        $this->component->incrementViews($posting, 'thread');

        $Entries = TableRegistry::get('Entries');
        $result = $Entries->find()
            ->select('views')
            ->where(['tid' => $tid])
            ->toArray();
        $this->assertEquals(1, array_shift($result)->get('views'));
        $this->assertEquals(0, array_shift($result)->get('views'));
    }
}
