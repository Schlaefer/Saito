<?php

namespace Saito\Test\User\LastRefresh;

use App\Controller\Component\CurrentUserComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Network\Request;
use Cake\Network\Response;
use Saito\Test\SaitoTestCase;
use Saito\User\LastRefresh\LastRefreshDatabase;

class LastRefreshDatabaseTest extends SaitoTestCase
{

    /**
     * @var CurrentUserComponent;
     */
    public $CurrentUser;

    public function setUp()
    {
        parent::setUp();

        $request = new Request();
        $request->session()->start();
        $request->session()->id('test');
        $response = new Response();
        $controller = new Controller($request, $response);
        $controller->loadComponent('Auth');
        $registry = new ComponentRegistry($controller);
        $this->CurrentUser = $this->getMock(
            'App\Controller\Component\CurrentUserComponent',
            ['_markOnline'],
            [$registry]
        );
        $this->LastRefresh = new LastRefreshDatabase($this->CurrentUser);
    }

    public function tearDown()
    {
        unset($this->CurrentUser);
        unset($this->LastRefresh);
        parent::tearDown();
    }

    /**
     * Tests that a newly registered users sees everything as new
     */
    public function testIsNewerThanForNewUsers()
    {
        $userData = ['id' => 1, 'last_refresh' => null];
        $this->CurrentUser->setSettings($userData);

        $this->assertNull($this->LastRefresh->isNewerThan(time()));
        $this->assertNull($this->LastRefresh->isNewerThan(date('Y-m-d h:i:s',
            time())));
    }

    /**
     * tests entry is newer than last refresh
     */
    public function testIsNewerThanTrue()
    {
        $time = time();
        $lastRefresh = date('Y-m-d H:i:s', $time + 10);
        $userData = ['id' => 1, 'last_refresh' => $lastRefresh];
        $this->CurrentUser->setSettings($userData);

        $this->assertTrue($this->LastRefresh->isNewerThan($time));
        $this->assertTrue($this->LastRefresh->isNewerThan(date('Y-m-d H:i:s',
            $time)));
    }

    /**
     * tests entry is older than last refresh
     */
    public function testIsNewerThanFalse()
    {
        $time = time();
        $lastRefresh = date('Y-m-d H:i:s', $time - 10);
        $userData = ['id' => 1, 'last_refresh' => $lastRefresh];
        $this->CurrentUser->setSettings($userData);

        $this->assertFalse($this->LastRefresh->isNewerThan($time));
        $this->assertFalse($this->LastRefresh->isNewerThan(date('Y-m-d H:i:s',
            $time)));
    }

}
