<?php

namespace App\Test\TestCase\Controller;

use App\Controller\EntriesController;
use Cake\Core\Configure;
use Cake\Database\Schema\Table;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Saito\Test\IntegrationTestCase;

class EntriesMockController extends EntriesController
{

    // @codingStandardsIgnoreStart
    public $uses = ['Entries'];

    // @codingStandardsIgnoreEnd

    public function getInitialThreads(
        $User,
        $order = ['Entry.last_answer' => 'DESC']
    ) {
        $this->_getInitialThreads($User, $order);
    }
}

/**
 * Class EntriesControllerTestCase
 *
 * @package App\Test\TestCase\Controller
 */
class EntriesControllerTestCase extends IntegrationTestCase
{

    /**
     * @var table for the controller
     */
    public $Table;

    public $fixtures = [
        'plugin.bookmarks.bookmark',
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.shout',
        'app.smiley',
        'app.smiley_code',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::get('Entries');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->Table);
    }

    public function testMixSuccess()
    {
        $this->get('/entries/mix/1');
        $this->assertResponseOk();

        $result = $this->viewVariable('titleForLayout');
        $this->assertStringStartsWith('First_Subject', $result);
    }

    public function testMixNoAuthorization()
    {
        $url = '/entries/mix/4';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testMixNotFound()
    {
        $this->setExpectedException('Cake\Network\Exception\NotFoundException');
        $this->get('/entries/mix/9999');
    }

    public function testMixRedirect()
    {
        $this->get('/entries/mix/7');
        $this->assertRedirect('/entries/mix/1#7');
    }

    /**
     * only logged in users should be able to answer
     */
    public function testAddUserNotLoggedInGet()
    {
        $url = '/entries/add';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    /**
     * successfull add request
     */
    public function testAddSuccess()
    {
        /*
        $C = $this->generate(
            'Entries',
            ['models' => ['Esevent' => ['notifyUserOnEvents']]]
        );
        */
        $this->_loginUser(1);
        $data = [
            'subject' => 'subject',
            'text' => 'text',
            'category_id' => 1,
            'Event' => [
                1 => ['event_type_id' => 0],
                2 => ['event_type_id' => 1]
            ]
        ];

        $EntriesTable = TableRegistry::get('Entries');
        $latestEntry = $EntriesTable->find()->order(['id' => 'desc'])->first();
        $expectedId = $latestEntry->get('id') + 1;

        /*
         * @td 3.0 Notif
        //* setup notification test
        $expected = [
            [
                'subject' => $expectedId,
                'event' => 'Model.Entry.replyToEntry',
                'receiver' => 'EmailNotification',
                'set' => 0,
            ],
            [
                'subject' => $expectedId,
                'event' => 'Model.Entry.replyToThread',
                'receiver' => 'EmailNotification',
                'set' => 1,
            ]
        ];

        $EventsTable = TableRegistry::get('Events');
        $C->Entry->Esevent->expects($this->once())
                ->method('notifyUserOnEvents')
                ->with(1, $expected);
        */

        //= test
        $this->mockSecurity();
        $this->post('/entries/add', $data);

        $this->assertResponseCode(302);
        $this->assertRedirect('/entries/view/' . $expectedId);

        $latestEntry = $EntriesTable->find()->order(['id' => 'desc'])->first();
        $this->assertEquals($expectedId, $latestEntry->get('id'));
    }

    public function testAddSubjectToLong()
    {
        $this->_loginUser(1);

        //= tests that the subject has a maxlength attribute
        $this->get('entries/add');
        $this->assertResponseContains('maxlength="40"');

        //= subject is one char to long
        $data = [
            // 41 chars
            'subject' => 'Vorher wie ich in der mobilen Version ka…',
            'category_id' => 1,
            'pid' => 0,
            /* @td 3.0 Notif
             * 'Event' => [
             * 1 => ['event_type_id' => 0],
             * 2 => ['event_type_id' => 1]
             * ]
             * */
        ];

        $this->mockSecurity();
        $this->post('entries/add', $data);
        $this->assertResponseContains('Subject is to long.');

        $nextId = $this->Table->find()->count() + 1;

        //= subject has max length
        $data['subject'] = mb_substr($data['subject'], 1);
        $this->post('entries/add', $data);

        $this->assertRedirect('entries/view/' . $nextId);
    }

    public function testNoDirectCallOfAnsweringFormWithId()
    {
        $this->_loginUser(1);
        $this->get('/entries/add/1');
        $this->assertRedirect('/');
    }

    public function testCategoryChooserVisible()
    {
        $this->_loginUser(1);
        $Users = TableRegistry::get('Users');
        $user = $Users->get(1);
        $element = 'btn-category-chooser';

        $user->set('user_category_override', false);
        $Users->save($user);

        // no global, no user allowed, no user
        Configure::write('Saito.Settings.category_chooser_global', 0);
        Configure::write('Saito.Settings.category_chooser_user_override', 0);
        $this->get('entries/index');
        $this->assertResponseNotContains($element);

        // global, no user allowed, no user
        Configure::write('Saito.Settings.category_chooser_global', 1);
        Configure::write('Saito.Settings.category_chooser_user_override', 0);
        $this->get('entries/index');
        $this->assertResponseContains($element);

        // no global, user allowed, no user
        Configure::write('Saito.Settings.category_chooser_global', 0);
        Configure::write('Saito.Settings.category_chooser_user_override', 1);
        $this->get('entries/index');
        $this->assertResponseNotContains($element);

        // no global, user allowed, user
        Configure::write('Saito.Settings.category_chooser_global', 0);
        Configure::write('Saito.Settings.category_chooser_user_override', 1);
        $user->set('user_category_override', true);
        $Users->save($user);
        $this->get('entries/index');
        $this->assertResponseContains($element);

        // global, not logged-in
        $this->_logoutUser();
        Configure::write('Saito.Settings.category_chooser_global', 1);
        $this->get('entries/index');
        $this->assertResponseNotContains($element);
    }

    public function testCategoryChooserSingle()
    {
        // = setup =
        $Users = TableRegistry::get('Users');
        Configure::write('Saito.Settings.category_chooser_global', 1);

        $this->_loginUser(1);
        $user = $Users->get(1);
        $user->set(
            [
                'user_sort_last_answer' => 1,
                'user_type' => 'admin',
                'user_category_active' => 4,
                'user_category_custom' => [
                    1 => 1,
                    2 => 1,
                    4 => 0,
                    9999 => 1
                ],
            ]
        );
        $Users->save($user);

        // = test =
        $this->get('entries/index');

        $this->assertEquals(4, $this->viewVariable('categoryChooserTitleId'));
        $this->assertEquals(
            [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '5' => '5',
            ],
            $this->viewVariable('categoryChooserChecked')
        );

        $entries = $this->viewVariable('entries');
        $filtered = array_filter(
            $entries,
            function ($entry) {
                return $entry->get('category')['id'] !== 4;
            }
        );
        $this->assertEmpty($filtered);
    }

    public function testCategoryChooserCustom()
    {
        // = setup =
        $Users = TableRegistry::get('Users');
        Configure::write('Saito.Settings.category_chooser_global', 1);

        $this->_loginUser(3);
        $user = $Users->get(3);
        $user->set(
            [
                'user_sort_last_answer' => 1,
                'user_category_active' => 0,
                'user_category_custom' => [1 => 1, 2 => 1, 4 => 0, 9999 => 1],
            ]
        );
        $Users->save($user);

        // = test =
        $this->get('entries/index');

        $this->assertEquals(
            'Custom',
            $this->viewVariable('categoryChooserTitleId')
        );
        // user should not see admin categories
        $this->assertEquals(
            [
                '2' => '2',
                '3' => '3',
                '5' => '5',
            ],
            $this->viewVariable('categoryChooserChecked')
        );
        $this->assertSame(
            $this->viewVariable('categoryChooser'),
            [
                3 => 'Another Ontopic',
                2 => 'Ontopic',
                4 => 'Offtopic',
                5 => 'Trash'
            ]
        );
        $entries = $this->viewVariable('entries');
        $filtered = array_filter(
            $entries,
            function ($entry) {
                return !in_array($entry->get('category')['id'], [2, 3, 4, 5]);
            }
        );
        $this->assertEmpty($filtered);
    }

    public function testDeleteNotLoggedIn()
    {
        $url = '/entries/delete/1';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    /*
    public function testDeleteWrongMethod()
    {
        $this->_loginUser(1);
        $this->setExpectedException(
            'Cake\Network\Exception\MethodNotAllowedException'
        );
        $this->get('/entries/delete/1');
    }
    */

    public function testDeleteNoId()
    {
        $this->_loginUser(1);
        $this->setExpectedException('Cake\Network\Exception\NotFoundException');
        $this->mockSecurity();
        $this->post('/entries/delete');
    }

    public function testDeleteSuccess()
    {
        $this->_loginUser(1);
        $Postings = TableRegistry::get('Entries');
        $count = count($Postings->treeForNode(1)->getAllChildren());
        $this->assertEquals(5, $count);

        $this->mockSecurity();
        $this->post('/entries/delete/9');

        $count = count($Postings->treeForNode(1)->getAllChildren());
        $this->assertEquals(3, $count);
    }

    public function testDeleteNoAuthorization()
    {
        $this->_loginUser(3);
        $this->post('/entries/delete/1');
        $this->assertRedirectContains('/login');
    }

    public function testDeletePostingDoesntExist()
    {
        $this->_loginUser(1);
        $this->setExpectedException('Cake\Network\Exception\NotFoundException');
        $this->mockSecurity();
        $this->post('/entries/delete/9999');
    }

    public function testIndexSuccess()
    {
        //* not logged in user
        $this->get('/entries/index');
        $postings = $this->viewVariable('entries');
        $this->assertEquals(count($postings), 2);
        $this->assertResponseOk();

        //* logged in user
        $this->_loginUser(3);
        $this->get('/entries/index');
        $postings = $this->viewVariable('entries');
        $this->assertEquals(count($postings), 4);
        $this->assertResponseOk();
    }

    public function testIndexSanitation()
    {
        $this->_loginUser(7);

        // uses contents to check in slidetabs
        $this->get('/entries/index');
        $this->assertResponseOk();
        $result = $this->_response->body();
        // uses <body>-HTML only: exclude <head> which may contain unescaped JS-data
        preg_match('/<body(.*)<\/body>/sm', $result, $matches);
        $result = $matches[0];
        $this->assertTextNotContains('&<Subject', $result);
        $this->assertTextContains('&amp;&lt;Subject', $result);
        $this->assertTextNotContains('&<Username', $result);
        $this->assertTextContains('&amp;&lt;Username', $result);
        // check for no double encoding
        $this->assertTextNotContains('&amp;amp;&amp;lt;Username', $result);
    }

    public function testMergeNoSourceId()
    {
        $mergeMethod = 'threadMerge';
        $this->assertTrue(method_exists($this->Table, $mergeMethod));
        $Entries = $this->getMockForTable('Entries', [$mergeMethod]);
        $Entries->expects($this->never())->method('threadMerge');

        $this->_loginUser(2);
        $this->mockSecurity();
        $this->setExpectedException('Cake\Network\Exception\NotFoundException');
        $this->post('/entries/merge/', ['targetId' => 2]);
    }

    public function testMergeSourceIdNotFound()
    {
        $mergeMethod = 'threadMerge';
        $this->assertTrue(method_exists($this->Table, $mergeMethod));
        $Entries = $this->getMockForTable('Entries', [$mergeMethod]);
        $Entries->expects($this->never())->method('threadMerge');

        $this->_loginUser(2);
        $this->mockSecurity();
        $this->setExpectedException('Cake\Network\Exception\NotFoundException');
        $this->post('/entries/merge/9999', ['targetId' => 2]);
    }

    public function testMergeShowForm()
    {
        $mergeMethod = 'threadMerge';
        $this->assertTrue(method_exists($this->Table, $mergeMethod));
        $Entries = $this->getMockForTable('Entries', [$mergeMethod]);
        $Entries->expects($this->never())->method('threadMerge');

        $this->_loginUser(2);
        $this->mockSecurity();
        $this->post('/entries/merge/4', []);
        $this->assertNoRedirect();
    }

    public function testMergeIsNotAuthorized()
    {
        $mergeMethod = 'threadMerge';
        $this->assertTrue(method_exists($this->Table, $mergeMethod));
        $Entries = $this->getMockForTable('Entries', [$mergeMethod]);
        $Entries->expects($this->never())->method('threadMerge');

        $this->_loginUser(3);
        $this->post('/entries/merge/4', ['targetId' => 2]);

        $this->assertRedirectContains('/login');
    }

    public function testMergeSuccess()
    {
        $mergeMethod = 'threadMerge';
        $this->assertTrue(method_exists($this->Table, $mergeMethod));
        $Entries = $this->getMockForTable('Entries', [$mergeMethod]);

        $Entries->expects($this->exactly(1))
            ->method('threadMerge')
            ->with(4, 2)
            ->will($this->returnValue(true));

        $this->_loginUser(2);
        $this->mockSecurity();
        $this->post('/entries/merge/4', ['targetId' => 2]);
    }

    /**
     * Entry does not exist
     */
    public function testEditNoEntry()
    {
        $this->_loginUser(2);
        $this->setExpectedException('Cake\Network\Exception\NotFoundException');
        $this->get('entries/edit/9999');
    }

    /**
     * Entry does not exist
     */
    public function testEditNoEntryId()
    {
        $this->_loginUser(2);
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('entries/edit/');
    }

    /**
     * Show editing form
     */
    public function testEditShowForm()
    {
        $postingId = 2;
        $this->_loginUser(1);
        $Table = TableRegistry::get('Entries');
        $Table->query()
            ->update()
            ->set(['time' => bDate()])
            ->where(['id' => $postingId])
            ->execute();

        $this->get("entries/edit/$postingId");

        // test that subject is quoted
        $this->assertResponseContains('value="Second_Subject"');
        // test that text is quoted
        $this->assertResponseContains('Second_Text</textarea>');

        /* @td 3.0 Notif
         * // notification are un/checked
         * $this->assertNotRegExp(
         * '/data\[Event\]\[1\]\[event_type_id\]"\s+?checked="checked"/',
         * $this->_response->body()
         * );
         * $this->assertRegExp(
         * '/data\[Event\]\[2\]\[event_type_id\]"\s+?checked="checked"/',
         * $this->_response->body()
         * );
         * */
    }

    public function testEditSuccess()
    {
        $postingId = 1;
        $this->_loginUser(3);
        $Table = TableRegistry::get('Entries');
        $Table->query()
            ->update()
            ->set(['time' => bDate()])
            ->where(['id' => $postingId])
            ->execute();

        $this->mockSecurity();
        $this->post(
            'entries/edit/' . $postingId,
            ['subject' => 'hot', 'text' => 'fuzz']
        );
        $this->assertResponseCode(302);
        $this->assertRedirect('/entries/view/' . $postingId);

        $Entries = TableRegistry::get('Entries');
        $posting = $Entries->get($postingId);
        $this->assertEquals('hot', $posting->get('subject'));
        $this->assertEquals('fuzz', $posting->get('text'));
    }

    /**
     * tests that the form renders without error if saving fails
     *
     * doesn't test for any specific validation error
     */
    public function testEditNoInternalErrorOnValidationError()
    {
        $Table = TableRegistry::get('Entries');
        $Table->query()
            ->update()
            ->set(['time' => bDate()])
            ->where(['id' => 2])
            ->execute();

        $Entries = $this->getMockForTable('Entries', ['update']);
        $Entries->expects($this->once())
            ->method('update')
            ->will($this->returnValue(false));

        $this->_loginUser(1);
        $this->mockSecurity();
        $this->post('entries/edit/2', ['pid' => 1]);
        $this->assertNoRedirect();
    }

    public function testPreviewNotLoggedIn()
    {
        $url = '/entries/preview';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testPreviewIsAjax()
    {
        $this->_loginUser(1);
        $this->setExpectedException(
            '\Cake\Network\Exception\BadRequestException',
            null,
            1434128359
        );
        $this->get('/entries/preview');
    }

    public function testPreviewIsPut()
    {
        $this->_setAjax();
        $this->_loginUser(1);
        $this->setExpectedException(
            '\Cake\Network\Exception\BadRequestException',
            null,
            1434128359
        );
        $this->get('/entries/preview');
    }

    public function testPreviewSuccess()
    {
        $this->_setJson();
        $this->_setAjax();
        $this->_loginUser(1);
        $data = [
            'pid' => 1,
            'category_id' => 2,
            'subject' => 'hot',
            'text' => 'fuzz'
        ];
        $this->mockSecurity();
        $this->post('/entries/preview', $data);
        $this->assertResponseOk();
        $this->assertNoRedirect();
        $this->assertResponseContains('fuzz');
    }

    /**
     * anon user views posting available for him
     */
    public function testViewNotLoggedInSuccess()
    {
        $this->_viewOk(1);
    }

    /*
     * anon users view posting not available to him
     */
    public function testViewNotLoggedInAuthFailure()
    {
        $url = '/entries/view/4';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    /**
     * logged-in user sees posting only available to logged-in users
     */
    public function testViewLoggedInAuthSuccess()
    {
        $this->_loginUser(3);
        $this->_viewOk(4);
    }

    public function testViewPostingDoesNotExistRedirect()
    {
        $this->get('/entries/view/9999');
        $this->assertRedirect('/');
    }

    /**
     * @param int $postingId
     */
    protected function _viewOk($postingId)
    {
        $this->get('/entries/view/' . $postingId);
        $this->assertResponseOk();
        $this->assertNoRedirect();
        $resultId = $this->viewVariable('entry')->get('id');
        $this->assertEquals($resultId, $postingId);
    }

    public function testViewIncreaseViewCounterNotLoggedIn()
    {
        $postingId = 1;

        $EntriesTable = TableRegistry::get('Entries');
        $posting = $EntriesTable->get($postingId);
        $viewsExpected = $posting->get('views') + 1;

        $this->get('/entries/view/' . $postingId);

        $posting = $EntriesTable->get($postingId);
        $viewsResult = $posting->get('views');

        $this->assertEquals(
            $viewsExpected,
            $viewsResult,
            'Posting view counter was not increased.'
        );
    }

    public function testViewIncreaseViewCounterLoggedIn()
    {
        $postingId = 1;

        $EntriesTable = TableRegistry::get('Entries');
        $posting = $EntriesTable->get($postingId);
        $viewsExpected = $posting->get('views') + 1;

        $this->_loginUser(1);
        $this->get('/entries/view/' . $postingId);

        $posting = $EntriesTable->get($postingId);
        $viewsResult = $posting->get('views');

        $this->assertEquals(
            $viewsExpected,
            $viewsResult,
            'Posting view counter was not increased.'
        );
    }

    /**
     * don't increase view counter if user views its own posting
     */
    public function testViewIncreaseViewCounterSameUser()
    {
        $postingId = 1;

        $EntriesTable = TableRegistry::get('Entries');
        $posting = $EntriesTable->get($postingId);
        $viewsExpected = $posting->get('views');

        $this->_loginUser(3);
        $this->get('/entries/view/' . $postingId);

        $posting = $EntriesTable->get($postingId);
        $viewsResult = $posting->get('views');

        $this->assertEquals($viewsExpected, $viewsResult);
    }

    /**
     * don't increase view counter on spiders/crawlers
     */
    public function testViewIncreaseViewCounterCrawler()
    {
        $this->_setUserAgent('A Crawler Agent');
        $postingId = 1;

        $EntriesTable = TableRegistry::get('Entries');
        $posting = $EntriesTable->get($postingId);
        $viewsExpected = $posting->get('views');

        $this->_loginUser(3);
        $this->get('/entries/view/' . $postingId);

        $posting = $EntriesTable->get($postingId);
        $viewsResult = $posting->get('views');

        $this->assertEquals($viewsExpected, $viewsResult);
    }

    public function testViewBoxFooter()
    {
        $this->get('entries/view/1');
        $this->assertResponseNotContains('panel-footer panel-form');

        $this->_loginUser(3);
        $this->get('entries/view/1');
        $this->assertResponseContains('panel-footer panel-form');
    }

    /**
     * Checks that the mod-button is in-/visible
     */
    public function testViewModButton()
    {
        /*
         * Mod Button is not visible for anon users
         */
        $this->get('entries/view/1');
        $this->assertResponseNotContains('dropdown');

        /*
         * Mod Button is not visible for normal users
         */
        $this->_loginUser(3);
        $this->get('entries/view/1');
        $this->assertResponseNotContains('dropdown');

        /*
         * Mod Button is visible for mods
         */
        $this->_loginUser(2);
        $this->get('entries/view/1');
        $this->assertResponseContains('dropdown');
    }

    public function testViewSanitation()
    {
        $this->_loginUser(1);
        $this->get('/entries/view/11');
        $this->assertResponseNotContains('&<Subject');
        $this->assertResponseContains('&amp;&lt;Subject');
        $this->assertResponseNotContains('&<Text');
        $this->assertResponseContains('&amp;&lt;Text');
        $this->assertResponseNotContains('&<Username');
        $this->assertResponseContains('&amp;&lt;Username');
    }

    public function testFeeds()
    {
        $this->get('/feed/postings.rss');
        $result = $this->viewVariable('entries');
        $first = $result->first();
        $this->assertEquals($first->get('subject'), 'First_Subject');
        $this->assertNull($first->get('ip'));

        $this->get('/feed/threads.rss');
        $result = $this->viewVariable('entries');
        $first = $result->first();
        $this->assertEquals($first->get('subject'), 'First_Subject');
        $this->assertNull($first->get('ip'));
    }

    public function testSolveNotLoggedIn()
    {
        $url = '/entries/solve/1';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testSolveNoEntry()
    {
        $this->_loginUser(1);
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('/entries/solve/9999');
    }

    public function testSolveNotRootEntryUser()
    {
        $this->_loginUser(2);
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('/entries/solve/1');
    }

    public function testSolveIsRootEntry()
    {
        $this->_loginUser(3);
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('/entries/solve/1');
    }

    public function testSolveSaveError()
    {
        $Entries = $this->getMockForTable('Entries', ['toggleSolve']);
        $this->_loginUser(3);
        $Entries->expects($this->once())
            ->method('toggleSolve')
            ->with('1')
            ->will($this->returnValue(false));
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->get('/entries/solve/1');
    }

    public function testSolve()
    {
        $Entries = $this->getMockForTable('Entries', ['toggleSolve']);
        $this->_loginUser(3);
        $Entries->expects($this->once())
            ->method('toggleSolve')
            ->with('1')
            ->will($this->returnValue(true));
        $this->get('/entries/solve/1');
        $this->assertResponseEquals('');
    }

    public function testSeo()
    {
        $this->get('/entries/index');
        $this->assertResponseNotContains('noindex');
        $url = Router::url('/', true);
        $expected = '<link rel="canonical" href="' . $url . '"/>';
        $this->assertResponseContains($expected);

        Configure::write('Saito.Settings.topics_per_page', 1);
        $this->get('/entries/index/?page=2');
        $this->assertResponseNotContains('rel="canonical"');
        $expected = '<meta name="robots" content="noindex, follow">';
        $this->assertResponseContains($expected);
    }

    public function testSourceNotLoggedIn()
    {
        $this->get('/entries/source/1');
        $this->assertRedirectContains('/login');
    }

    public function testSourceSuccess()
    {
        $this->_loginUser(3);
        $this->get('/entries/source/1');
        $this->assertResponseContains("First_Subject\n\nFirst_Text");
    }

    public function testThreadLineAnon()
    {
        $this->get('/entries/threadLine/6.json');
        $this->assertRedirectContains('/login');
    }

    public function testThreadLineForbidden()
    {
        $this->_loginUser(3);
        $this->get('/entries/threadLine/6.json');
        $this->assertRedirectContains('/login');
    }

    public function testThreadLineSucces()
    {
        $this->_loginUser(1);
        $this->get('/entries/threadLine/6.json');
        $this->assertNoRedirect();
        $expected = 'Third Thread First_Subject';
        $this->assertResponseContains($expected);
    }
}
