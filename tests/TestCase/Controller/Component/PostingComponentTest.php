<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\PostingComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Saito\Posting\Posting;
use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

class PostingComponentTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Draft',
        'app.Entry',
        'app.User',
        'plugin.Bookmarks.Bookmark',
    ];

    /**
     * @var PostingComponent
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
        $this->component = new PostingComponent($registry);
    }

    public function tearDown()
    {
        parent::tearDown();
        // Clean up after we're done
        unset($this->component, $this->controller);
    }

    public function testCreateUserThreadDisallowed()
    {
        $thread = ['subject' => 'foo', 'category_id' => 4];

        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $this->expectException(ForbiddenException::class);

        $this->component->create($thread, $user);
    }

    public function testCreateUserAnswerDisallowed()
    {
        $answer = ['pid' => 6, 'subject' => 'foo'];
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $this->expectException(ForbiddenException::class);

        $result = $this->component->create($answer, $user);
    }

    public function testCreateUserAnswerAllowed()
    {
        $answer = ['pid' => 11, 'subject' => 'foo', 'name' => 'foo', 'user_id' => 100];

        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $posting = $this->component->create($answer, $user);

        $errors = $posting->getErrors();
        $this->assertEmpty($errors);
    }

    public function testCreateAdminAllowed()
    {
        $admin = ['id' => 101, 'username' => 'foo', 'user_type' => 'admin'];
        $admin = CurrentUserFactory::createLoggedIn($admin);

        $thread = ['subject' => 'foo', 'category_id' => 4, 'name' => 'foo', 'user_id' => 101];
        $answer = ['pid' => 11] + $thread;

        foreach ([$thread, $answer] as $data) {
            $posting = $this->component->create($answer, $admin);

            $this->assertEmpty($posting->getErrors());
        }
    }

    public function testCreateParentDoesNotExist()
    {
        $answer = ['pid' => 9999, 'subject' => 'foo'];
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1564756571);

        $this->component->create($answer, $user);
    }

    public function testCreateNewThreadButNoCategoryProvided()
    {
        $answer = ['subject' => 'foo'];
        $user = ['id' => 100, 'username' => 'foo', 'user_type' => 'user'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $this->expectException(\InvalidArgumentException::class);

        $result = $this->component->create($answer, $user);
    }

    public function testUpdateSuccesModOnPinnedPosting()
    {
        $now = (string)time();
        $edit = ['subject' => $now];

        $table = TableRegistry::getTableLocator()->get('Entries');
        $entity = $table->findById(11)->first();

        $user = ['id' => 7, 'user_type' => 'mod', 'username' => 'bar'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $result = $this->component->update($entity, $edit, $user);

        $this->assertEmpty($result->getErrors());
        $this->assertEquals($now, $result->get('subject'));
    }

    public function testUpdateFailureModOnOwnPosting()
    {
        $now = (string)time();
        $edit = ['subject' => $now];

        $table = TableRegistry::getTableLocator()->get('Entries');
        $entity = $table->findById(11)->first();
        $entity->set('fixed', false);

        $user = ['id' => 7, 'user_type' => 'mod', 'username' => 'bar'];
        $user = CurrentUserFactory::createLoggedIn($user);

        $this->expectException(ForbiddenException::class);

        $this->component->update($entity, $edit, $user);
    }

    public function testPrepareChildPosting()
    {
        $parent = [
            'id' => 123,
            'category_id' => 456,
            'subject' => 'parent subject',
            'tid' => 789,
        ];
        $parent = new Posting($parent);

        $data = $this->component->prepareChildPosting($parent, []);

        $this->assertEquals(456, $data['category_id']);
        $this->assertEquals('parent subject', $data['subject']);
        $this->assertEquals(789, $data['tid']);
    }
}
