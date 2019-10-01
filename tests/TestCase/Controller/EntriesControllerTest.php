<?php

namespace App\Test\TestCase\Controller;

use App\Controller\EntriesController;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Schema\Table;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Saito\Exception\SaitoForbiddenException;
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
        'plugin.Bookmarks.Bookmark',
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.Smiley',
        'app.SmileyCode',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserOnline',
        'app.UserRead'
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
        $this->expectException('Cake\Http\Exception\NotFoundException');
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

    public function testAddShowForumSuccess()
    {
        $this->_loginUser(1);
        $this->get('/entries/add');
        $this->assertResponseCode(200);
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
        $this->expectException(ForbiddenException::class);

        $this->get('/entries/delete/1');
    }

    /*
    public function testDeleteWrongMethod()
    {
        $this->_loginUser(1);
        $this->expectException(
            'Cake\Network\Exception\MethodNotAllowedException'
        );
        $this->get('/entries/delete/1');
    }
    */

    public function testDeleteNoId()
    {
        $this->_loginUser(1);
        $this->expectException('Cake\Http\Exception\NotFoundException');
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
        $this->expectException(ForbiddenException::class);

        $this->post('/entries/delete/1');
    }

    public function testDeletePostingDoesntExist()
    {
        $this->_loginUser(1);
        $this->expectException('Cake\Http\Exception\NotFoundException');
        $this->mockSecurity();
        $this->post('/entries/delete/9999');
    }

    public function testIndexSuccessAnonoymous()
    {
        /*
         * fix for sudden and unclear "Call to a member function flock() on null
         * vendor/cakephp/cakephp/src/Cache/Engine/FileEngine.php on 157" error
         */
        Cache::clearAll();

        $this->get('/entries/index');
        $postings = $this->viewVariable('entries');
        $this->assertCount(3, $postings);
        $this->assertResponseOk();
    }

    public function testIndexSuccessLoggedInUser()
    {
        $this->_loginUser(3);
        $this->get('/entries/index');
        $postings = $this->viewVariable('entries');
        $this->assertCount(5, $postings);
        $this->assertResponseOk();
    }

    public function testIndexSanitation()
    {
        $this->_loginUser(7);

        // uses contents to check in slidetabs
        $this->get('/entries/index');
        $this->assertResponseOk();
        $result = $this->_response->getBody();
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
        $this->expectException('Cake\Http\Exception\NotFoundException');
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
        $this->expectException('Cake\Http\Exception\NotFoundException');
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

        $this->expectException(ForbiddenException::class);

        $this->_loginUser(3);
        $this->post('/entries/merge/4', ['targetId' => 2]);
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
        $this->expectException('Cake\Http\Exception\NotFoundException');
        $this->get('entries/edit/9999');
    }

    /**
     * Entry does not exist
     */
    public function testEditNoEntryId()
    {
        $this->_loginUser(2);
        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->get('entries/edit/');
    }

    public function testEditShowSuccess()
    {
        $this->_loginUser(1);
        $this->get('entries/edit/1');

        $this->assertResponseCode(200);
    }

    public function testEditShowFailureForbidden()
    {
        $this->_loginUser(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->get('entries/edit/1');
    }

    /**
     * Edit message should not be shown. Edit time is below settings treshold.
     */
    public function testViewEditNoticeIsNotShown()
    {
        $this->_loginUser(1);
        Configure::write('Saito.Settings.edit_delay', '3');
        $Table = TableRegistry::get('Entries');
        $posting = $Table->findById(3)->first();
        $editDelay = Configure::read('Saito.Settings.edit_delay');
        $posting->set('edited', $posting->get('time')->addMinutes($editDelay)->addSeconds(-1));
        $posting->set('edited_by', $posting->get('name'));
        $Table->save($posting);

        $this->get('/entries/view/' . $posting->get('id'));

        $this->assertResponseOk();
        $this->assertResponseNotContains('edited by');
    }

    /**
     * Edit message should be shown. Edit time is above settings treshold.
     */
    public function testViewEditNoticeIsShown()
    {
        $this->_loginUser(1);

        Configure::write('Saito.Settings.edit_delay', '3');
        $Table = TableRegistry::get('Entries');
        $posting = $Table->findById(3)->first();
        $editDelay = Configure::read('Saito.Settings.edit_delay');
        $posting->set('edited', $posting->get('time')->addMinutes($editDelay)->addSeconds(1));
        $posting->set('edited_by', $posting->get('name'));
        $Table->save($posting);

        $this->get('/entries/view/' . $posting->get('id'));

        $this->assertResponseOk();
        $this->assertResponseContains('edited by ' . $posting->get('author'));
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

    /**
     * Checks that the mod-button is in-/visible
     */
    public function testViewModButton()
    {
        /*
         * Mod Button is not visible for anon users
         */
        $this->get('/entries/view/1');
        $this->assertResponseNotContains('dropdown');

        /*
         * Mod Button is not visible for normal users
         */
        $this->_loginUser(3);
        $this->get('/entries/view/1');
        $this->assertResponseNotContains('dropdown');

        /*
         * Mod Button is visible for mods
         */
        $this->_loginUser(2);
        $this->get('/entries/view/1');
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

    public function testSolveNotLoggedIn()
    {
        $url = '/entries/solve/1';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testSolveNoEntry()
    {
        $this->_loginUser(1);
        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->get('/entries/solve/9999');
    }

    public function testSolveNotRootEntryDoesntBelongToCurrentUser()
    {
        $this->_loginUser(2);
        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->get('/entries/solve/2');
    }

    public function testSolveIsRootEntry()
    {
        $this->_loginUser(3);
        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->get('/entries/solve/1');
    }

    public function testSolveSaveError()
    {
        $Entries = $this->getMockForTable('Entries', ['toggleSolve']);
        $this->_loginUser(3);
        $Entries->expects($this->once())
            ->method('toggleSolve')
            ->will($this->returnValue(false));
        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->get('/entries/solve/2');
    }

    public function testSolveSuccess()
    {
        $Entries = $this->getMockForTable('Entries', ['toggleSolve']);
        $this->_loginUser(3);
        $Entries->expects($this->once())
            ->method('toggleSolve')
            ->will($this->returnValue(true));
        $this->get('/entries/solve/2');
        $this->assertResponseOk();
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
        $this->_setJson();
        $this->get('/entries/threadline/6');
        $this->assertRedirectContains('/login');
    }

    public function testThreadLineForbidden()
    {
        $this->_loginUser(3);
        $this->_setJson();
        $this->get('/entries/threadline/6');
        $this->assertRedirectContains('/login');
    }

    public function testThreadLineSucces()
    {
        $this->_loginUser(1);
        $this->_setJson();
        $this->get('/entries/threadline/6');
        $this->assertNoRedirect();
        $expected = 'Third Thread First_Subject';
        $this->assertResponseContains($expected);
    }
}
