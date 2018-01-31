<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\ThreadsComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\SaitoTestCase;

/**
 * Class ThemesComponentTest
 *
 * @package App\Test\TestCase\Controller\Component
 */
class ThreadsComponentTest extends SaitoTestCase
{
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.user'
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
        unset($this->component);
        parent::tearDown();
    }


    public function testThreadIncrementView()
    {
        $tid = 4;

        $CU = $this->getMockBuilder('\Saito\User\SaitoUser')
            ->setMethods(['isBot'])
            ->getMock();
        $CU->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        Registry::set('CU', $CU);

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
        $CU = $this->getMockBuilder('\Saito\User\SaitoUser')
            ->setMethods(['isBot'])
            ->getMock();
        $CU->setSettings(['id' => 3]);

        $CU->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        Registry::set('CU', $CU);

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
