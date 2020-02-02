<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\ThreadsComponent;
use App\Model\Table\EntriesTable;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Response;
use Cake\Network\Request;
use Saito\Test\Model\Table\SaitoTableTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

/**
 * Class ThemesComponentTest
 *
 * @package App\Test\TestCase\Controller\Component
 */
class ThreadsComponentTest extends SaitoTableTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.User',
        'plugin.Bookmarks.Bookmark',
    ];

    /**
     * @var ThreadsComponent
     */
    public $component;

    /**
     * @var Controller
     */
    public $controller;

    public $tableClass = 'Entries';

    /** @var EntriesTable */
    public $Table;

    public function setUp(): void
    {
        parent::setUp();
        // Setup our component and fake test controller
        $request = new Request();
        $response = new Response();
        $this->controller = new Controller($request, $response);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new ThreadsComponent($registry, ['table' => $this->Table]);
    }

    public function tearDown(): void
    {
        unset($this->component, $this->controller);
        parent::tearDown();
    }

    public function testIncrementViewForPosting()
    {
        $tid = 4;

        $this->component->AuthUser = $this->getMockBuilder(AuthUserComponent::class)
            ->setMethods(['isBot'])
            ->getMock();
        $this->component->AuthUser->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        $CU = CurrentUserFactory::createDummy();

        $posting = $this->Table->get(4);

        $this->component->incrementViewsForPosting($posting, $CU);

        $result = $this->Table->find()
            ->select('views')
            ->where(['tid' => $tid])
            ->toArray();
        $this->assertEquals(1, array_shift($result)->get('views'));
        $this->assertEquals(0, array_shift($result)->get('views'));
    }

    public function testIncrementViewForPostingOmmitUser()
    {
        $tid = 4;

        $this->component->AuthUser = $this->getMockBuilder(AuthUserComponent::class)
            ->setMethods(['isBot'])
            ->getMock();
        $this->component->AuthUser->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        $CU = CurrentUserFactory::createDummy(['id' => 1]);
        $posting = $this->Table->get(4);

        $this->component->incrementViewsForPosting($posting, $CU);

        $result = $this->Table->find()
            ->select('views')
            ->where(['tid' => $tid])
            ->toArray();
        $this->assertEquals(0, array_shift($result)->get('views'));
        $this->assertEquals(0, array_shift($result)->get('views'));
    }

    public function testIncrementViewForThread()
    {
        $tid = 4;

        $this->component->AuthUser = $this->getMockBuilder(AuthUserComponent::class)
            ->setMethods(['isBot'])
            ->getMock();
        $this->component->AuthUser->expects($this->once())->method('isBot')->will(
            $this->returnValue(false)
        );
        $CU = CurrentUserFactory::createDummy();

        $posting = $this->Table->get(4);

        $this->component->incrementViewsForThread($posting, $CU);

        $result = $this->Table->find()
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
        $CU = CurrentUserFactory::createDummy(['id' => 3]);

        $posting = $this->Table->get(4);

        $this->component->incrementViewsForThread($posting, $CU);

        $result = $this->Table->find()
            ->select('views')
            ->where(['tid' => $tid])
            ->toArray();
        $this->assertEquals(1, array_shift($result)->get('views'));
        $this->assertEquals(0, array_shift($result)->get('views'));
    }
}
