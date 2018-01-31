<?php

namespace Bookmarks\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Saito\Test\IntegrationTestCase;

class BookmarksControllerTest extends IntegrationTestCase
{

    public $fixtures = [
        'app.category',
        'app.entry',
        'app.setting',
        'app.smiley',
        'app.smiley_code',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    /**
     * @var \Cake\ORM\Table
     */
    protected $Bookmarks;

    public function setUp()
    {
        $this->Bookmarks = TableRegistry::get('Bookmarks');
        parent::setUp();
    }

    public function testIndexNotAllowed()
    {
        $url = '/bookmarks/index';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testIndex()
    {
        $this->_loginUser(3);
        $this->get('/bookmarks/index');

        $bookmarks = $this->Bookmarks->find();
        // 5 belongs to user but is disallowed category
        $allowed = [1, 2];
        foreach ($bookmarks as $bookmark) {
            $id = $bookmark->get('id');
            $edit = 'bookmarks/edit/' . $id;
            if (in_array($id, $allowed)) {
                $this->assertResponseContains($edit);
            } else {
                $this->assertResponseNotContains($edit);
            }
        }

        // check that output is sanitized
        $this->assertResponseContains('&lt; Comment 2');
    }

    public function testAddNoAjax()
    {
        $this->_loginUser(5);
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->disableCsrf();
        $this->post('/bookmarks/add', ['id' => 1]);
    }

    public function testAddNoPost()
    {
        $this->_loginUser(5);
        $this->_setAjax();
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('/bookmarks/add');
    }

    public function testAddSuccess()
    {
        $this->_loginUser(5);
        $this->_setAjax();

        $before = $this->Bookmarks->find()->count();
        $this->post('/bookmarks/add', ['id' => 1]);

        $after = $this->Bookmarks->find()->count();
        $this->assertEquals($before + 1, $after);
        $this->assertTrue(
            $this->Bookmarks->exists(['entry_id' => 1, 'user_id' => 5])
        );
    }

    public function testEditNotLoggedIn()
    {
        $url = '/bookmarks/edit/1';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testEditNotUsersBookmark()
    {
        $this->_loginUser(1);
        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
        $this->get('/bookmarks/edit/1');
    }

    public function testEditRead()
    {
        $this->_loginUser(3);
        $this->get('/bookmarks/edit/2');

        $this->assertEquals(
            $this->viewVariable('bookmark')->get('comment'),
            '< Comment 2'
        );

        // special chars are escaped
        $this->assertResponseContains('&lt; Comment 2');
        $this->assertResponseNotContains('< Comment 2');
        $this->assertResponseContains('&lt;Subject');
        $this->assertResponseNotContains('<Subject');
        $this->assertResponseContains('&lt;Text');
        $this->assertResponseNotContains('<Text');
    }

    public function testEditSave()
    {
        $this->mockSecurity();
        $this->_loginUser(3);
        $comment = date('c');

        $data = ['comment' => $comment];
        $this->post('/bookmarks/edit/1', $data);
        $bookmark = $this->Bookmarks->get(1);

        $this->assertSame($comment, $bookmark->get('comment'));
        $this->assertRedirect('/bookmarks#1');
    }

    public function testDeleteNoAjax()
    {
        $this->_loginUser(3);
        $this->setExpectedException('\Cake\Network\Exception\BadRequestException');
        $this->disableCsrf();
        $this->delete('/bookmarks/delete/1');
    }

    public function testDeleteNotUsersBookmark()
    {
        $this->_setAjax();
        $this->_loginUser(1);
        $this->mockSecurity();

        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
        $this->delete('/bookmarks/delete/1');
    }

    public function testDelete()
    {
        $this->assertTrue($this->Bookmarks->exists(['id' => 1]));

        $this->_loginUser(3);
        $this->_setAjax();
        $this->mockSecurity();
        $this->delete('/bookmarks/delete/1');

        $this->assertFalse($this->Bookmarks->exists(['id' => 1]));
    }

    public function testViewIsBookmarked()
    {
        $this->_loginUser(3);
        $this->get('/entries/view/1');
        $this->assertResponseContains('isBookmarked":true');
    }

    public function testViewIsNotBookmarked()
    {
        $this->_loginUser(3);
        $this->get('/entries/view/2');
        $this->assertResponseOk();
        $this->assertResponseContains('isBookmarked":false');
    }
}
