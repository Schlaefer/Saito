<?php

namespace App\Test\TestCase\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Filesystem\Folder;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;
use Saito\User\Permission\ResourceAC;

class UsersControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Draft',
        'app.Entry',
        'app.Setting',
        'app.Smiley',
        'app.SmileyCode',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserOnline',
        'app.UserRead',
        'plugin.Bookmarks.Bookmark',
        'plugin.ImageUploader.Uploads',
    ];

    public function testAdminAddSuccess()
    {
        $this->mockSecurity();
        $data = [
            'username' => 'foo',
            'user_email' => 'fo3@example.com',
            'password' => 'test',
            'password_confirm' => 'test',
        ];
        $expected = [
            'username' => 'foo',
            'user_email' => 'fo3@example.com',
        ];

        $Users = TableRegistry::get('Users');
        $before = $Users->find()->count();

        $this->_loginUser(1);
        $this->post('/admin/users/add', $data);

        $this->assertEquals($before + 1, $Users->find()->count());

        $user = $Users->find()->order(['id' => 'DESC'])->first();
        foreach (array_keys($expected) as $field) {
            $this->assertEquals($expected[$field], $user->get($field));
        }

        $auth = new DefaultPasswordHasher();
        $this->assertTrue($auth->check('test', $user->get('password')));

        $this->assertRedirect('/users/view/' . $user->get('id'));
    }

    public function testAdminAddNoAccess()
    {
        $url = '/admin/users/add';
        $this->post($url);
        $this->assertRedirectLogin($url);
    }

    public function testLogin()
    {
        $data = ['username' => 'Ulysses', 'password' => 'test'];

        $this->get('/');
        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNull(
            $this->_controller->request->getSession()->read('Auth')
        );

        $this->mockSecurity();
        $this->post('/login', $data);

        $this->assertFalse($this->_controller->components()->has('Security'));

        $this->assertTrue($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNotNull(
            $this->_controller->request->getSession()->read('Auth')
        );

        //# successful login redirects
        $this->assertRedirect('/');

        //# last login time should be set
        $Users = TableRegistry::get('Users');
        $user = $Users->get(3, ['fields' => 'last_login']);
        $this->assertWithinRange($user->get('last_login')->toUnixString(), time(), 2);
    }

    public function testLoginShowForm()
    {
        //# show login form
        $this->get('/login');
        $this->assertResponseSuccess();
        $this->assertNoRedirect();

        //## test username field
        $username = [
            'input#tf-login-username' => [
                'attributes' => [
                    'autocomplete' => 'username',
                    'name' => 'username',
                    'required' => 'required',
                    'tabindex' => '100',
                    'type' => 'text',
                ],
            ],
            'input#password' => [
                'attributes' => [
                    'autocomplete' => 'current-password',
                    'name' => 'password',
                    'required' => 'required',
                    'tabindex' => '101',
                    'type' => 'password',
                ],
            ],
        ];
        $this->assertContainsTag($username, (string)$this->_response->getBody());

        //# test logout on form show
        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $user = $this->_loginUser(3);
        $this->_controller->CurrentUser->setSettings($user);
        $this->assertTrue($this->_controller->CurrentUser->isLoggedIn());

        $this->get('/login');

        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
    }

    public function testLoginUserNotActivated()
    {
        $this->mockSecurity();
        $data = ['username' => 'Diane', 'password' => 'test'];
        $result = $this->post('/login', $data);
        $this->assertResponseContains('is not activated yet.', $result);
    }

    public function testLoginUserLocked()
    {
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');
        $UserBlocks = $this->getMockForTable(
            'UserBlocks',
            ['getBlockEndsForUser']
        );
        $UserBlocks
            ->expects($this->once())
            ->method('getBlockEndsForUser')
            ->with('8')
            ->will($this->returnValue(false));
        $Users->UserBlocks = $UserBlocks;
        $data = ['username' => 'Walt', 'password' => 'test'];
        $this->post('/login', $data);
        $this->assertResponseContains('is locked.');
    }

    public function testRegisterEmailFailed()
    {
        $this->mockSecurity();

        $transporter = $this->mockMailTransporter();
        $transporter
            ->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Exception()));

        Configure::write('Saito.Settings.tos_enabled', false);
        $data = [
            'username' => 'NewUser1',
            'user_email' => 'NewUser1@example.com',
            'password' => 'NewUser1spassword',
            'password_confirm' => 'NewUser1spassword',
        ];

        /*
        $Users->User->expects($this->once())
            ->method('register')
            ->will($this->returnValue(true));
        $Users->SaitoEmail->expects($this->once())
            ->method('email')
            ->will($this->throwException(new Exception));
        */

        $this->post('users/register', $data);
        $this->assertResponseContains('Sending Confirmation Email Failed');

        $Users = TableRegistry::get('Users');
        $exists = $Users->exists(
            [
                'username' => 'NewUser1',
                'activate_code >' => 0,
            ]
        );
        $this->assertTrue($exists);
    }

    public function testRegisterViewFormSuccess()
    {
        $this->get('/users/register');

        $this->assertResponseOk();

        $expected = [
            'input#username' => [
                'attributes' => [
                    'autocomplete' => 'username',
                    'name' => 'username',
                    'required' => 'required',
                    'tabindex' => '1',
                    'type' => 'text',
                ],
            ],
            'input#user-email' => [
                'attributes' => [
                    'autocomplete' => 'email',
                    'name' => 'user_email',
                    'required' => 'required',
                    'tabindex' => '2',
                    'type' => 'text',
                ],
            ],
            'input#password' => [
                'attributes' => [
                    'autocomplete' => 'new-password',
                    'name' => 'password',
                    'tabindex' => '3',
                    'type' => 'password',
                ],
            ],
            'input#password-confirm' => [
                'attributes' => [
                    'autocomplete' => 'new-password',
                    'name' => 'password_confirm',
                    'tabindex' => '4',
                    'type' => 'password',
                ],
            ],
        ];
        $this->assertContainsTag($expected, (string)$this->_response->getBody());
    }

    public function testRegisterViewFormFailureNoPermission()
    {
        Configure::read('Saito.Permission.Resources')
            ->get('saito.core.user.register')
            ->disallow((new ResourceAC())->asEverybody());
        $this->expectException(SaitoForbiddenException::class);
        $this->get('/users/register');
    }

    public function testRegisterCheckboxNotOnPage()
    {
        Configure::write('Saito.Settings.tos_enabled', false);
        $this->get('users/register');

        $this->assertResponseOk();
        $this->assertResponseNotContains('tos_confirm');
        $this->assertResponseNotContains('http://example.com/tos-url.html/');
        $this->assertResponseNotContains('id="tosConfirm"');
    }

    public function testRegisterCheckboxOnPage()
    {
        $this->get('users/register');
        $this->assertResponseContains('tos_confirm');
        $this->assertResponseContains('http://example.com/tos-url.html/');
        $this->assertResponseContains('id="tosConfirm"');
    }

    public function testRegisterCheckboxOnPageCustomTosUrl()
    {
        Configure::write('Saito.Settings.tos_url', '');
        $this->get('users/register');
        $this->assertResponseContains(
            $this->_controller->request->getAttribute('webroot') . 'pages/en/tos'
        );
    }

    /**
     * test TOS checkbox
     */
    public function testRegisterTosActive()
    {
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');

        /*
         * TOS not checked
         */
        $data = [
            'username' => 'NewUser1',
            'user_email' => 'NewUser1@example.com',
            'password' => 'NewUser1spassword',
            'password_confirm' => 'NewUser1spassword',
            'tos_confirm' => '0',
        ];

        $exists = $Users->exists(['username' => 'NewUser1']);
        $this->assertFalse($exists);

        $this->post('users/register', $data);

        /*
         * TOS checked
         */
        $transporter = $this->mockMailTransporter();
        $transporter
            ->expects($this->at(0))
            ->method('send')
            ->with(
                $this->callback(
                    function (Email $email) use ($Users) {
                        $this->assertEquals(
                            $email->getFrom(),
                            ['register@example.com' => 'macnemo']
                        );
                        $this->assertEquals(
                            $email->getTo(),
                            ['NewUser1@example.com' => 'NewUser1']
                        );

                        $user = $Users->find()
                            ->where(['username' => 'NewUser1'])
                            ->first();
                        $id = $user->get('id');
                        $activate = $user->get('activate_code');
                        $this->assertStringContainsString(
                            "/users/rs/$id?c=$activate",
                            implode(' ', $email->message())
                        );

                        return true;
                    }
                )
            );

        $data['tos_confirm'] = '1';
        $this->post('users/register', $data);

        $exists = $Users->exists(
            [
                'username' => 'NewUser1',
                'activate_code >' => 0,
            ]
        );
        $this->assertTrue($exists);
    }

    /**
     * No TOS flag isn't send, but it's also not necessary
     */
    public function testRegisterTosNotActive()
    {
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');
        Configure::write('Saito.Settings.tos_enabled', false);

        $data = [
            'username' => 'NewUser1',
            'user_email' => 'NewUser1@example.com',
            'password' => 'NewUser1spassword',
            'password_confirm' => 'NewUser1spassword',
        ];
        $this->post('users/register', $data);

        $exists = $Users->exists(
            [
                'username' => 'NewUser1',
                'activate_code >' => 0,
            ]
        );
        $this->assertTrue($exists);
    }

    /**
     * Test all failing register validations
     */
    public function testRegisterValidation()
    {
        $this->mockSecurity();
        Configure::write('Saito.Settings.tos_enabled', false);

        $data = [
            'username' => "mITch",
            'user_email' => 'alice@example.com',
            'password' => 'NewUserspassword',
            'password_confirm' => 'NewUser1spassword',
        ];

        $Users = TableRegistry::get('Users');
        $before = $Users->find()->count();
        $this->assertGreaterThan(0, $before);

        $this->post('users/register', $data);

        $after = $Users->find()->count();
        $this->assertEquals($after, $before);

        $this->assertResponseOk();

        // Test that error strings are shown
        $this->assertResponseContains('Email address is already used.');
        $this->assertResponseContains('Passwords don&#039;t match.');
        $this->assertResponseContains('Name is already used.');
    }

    public function testRsSuccess()
    {
        $Users = TableRegistry::get('Users');
        $user = $Users->get(10);

        $this->assertEquals(1548, $user->get('activate_code'));
        $this->get('/users/rs/10/?c=1548');

        $user = $Users->get(10);
        $this->assertEquals(0, $user->get('activate_code'));
    }

    public function testRsFailureNoPermission()
    {
        Configure::read('Saito.Permission.Resources')
            ->get('saito.core.user.register')
            ->disallow((new ResourceAC())->asEverybody());
        $this->expectException(SaitoForbiddenException::class);
        $this->get('/users/rs/10/?c=1549');
    }

    public function testRsFailureWrongCode()
    {
        $Users = TableRegistry::get('Users');
        $user = $Users->get(10);

        $this->assertEquals(1548, $user->get('activate_code'));
        $this->get('/users/rs/10/?c=1549');

        $user = $Users->get(10);
        $this->assertEquals(1548, $user->get('activate_code'));
    }

    public function testSetcategoryNotLoggedIn()
    {
        $url = '/users/setcategory/all';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testSetcategoryAll()
    {
        $userId = 3;
        $category = 'all';
        $this->_loginUser($userId);

        EventManager::instance()->on(
            'Controller.startup',
            function (Event $event) use ($userId, $category) {
                $Users = $this->getMockForTable('Users', ['setCategory']);
                $Users->expects($this->once())
                    ->method('setCategory')
                    ->with($userId, $category);

                $controller = $event->getSubject();
                $controller->Users = $Users;
            }
        );

        $this->get('/users/setcategory/' . $category);
    }

    public function testSetcategoryCategory()
    {
        $userId = 3;
        $category = 5;
        $this->_loginUser($userId);

        EventManager::instance()->on(
            'Controller.startup',
            function (Event $event) use ($userId, $category) {
                $Users = $this->getMockForTable('Users', ['setCategory']);
                $Users->expects($this->once())
                    ->method('setCategory')
                    ->with($userId, $category);

                $controller = $event->getSubject();
                $controller->Users = $Users;
            }
        );

        $this->get('/users/setcategory/' . $category);
    }

    public function testSetcategoryCategories()
    {
        $userId = 3;
        $data = [
            'CatChooser' => [
                '4' => '0',
                '7' => '1',
                '9' => '0',
            ],
            'CatMeta' => [
                'All' => '1',
            ],
        ];

        $this->mockSecurity();
        $this->_loginUser($userId);
        EventManager::instance()->on(
            'Controller.startup',
            function (Event $event) use ($userId, $data) {
                $Users = $this->getMockForTable('Users', ['setCategory']);
                $Users->expects($this->once())
                    ->method('setCategory')
                    ->with($userId, $data['CatChooser']);

                $controller = $event->getSubject();
                $controller->Users = $Users;
            }
        );

        $this->post('/users/setcategory/', $data);
    }

    public function testSlidetabOrderSet()
    {
        $this->_loginUser(3);

        $Users = TableRegistry::get('Users');
        $user = $Users->get(3);

        $validData = ['slidetab_userlist'];
        $expected = serialize($validData);

        $this->assertNotEquals($expected, $user->get('slidetabOrder'));

        $data = $validData;
        $data[] = ['slidetab_foo'];
        $this->_setAjax();
        $this->post('/users/slidetabOrder', ['slidetabOrder' => $data]);

        $this->assertResponseOk();
        $this->assertResponseContains('1');

        $user = $Users->get(3);
        $this->assertEquals($expected, $user->get('slidetab_order'));
    }

    public function testViewProfileRequestByUsername()
    {
        $this->_loginUser(3);
        $this->get('/users/view/Mitch');
        $this->assertRedirect('/users/name/Mitch');
    }

    public function testViewProfileForbiddenForAnon()
    {
        $url = '/users/view/1';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testViewProfileDoesNotExist()
    {
        $this->_loginUser(3);
        $this->get('/users/view/9999');
        $this->assertRedirect('/');
    }

    public function testViewProfileSuccess()
    {
        $userId = 3;
        $UsersTable = $this->getMockForTable('Users', ['countSolved']);
        $UsersTable->expects($this->once())
            ->method('countSolved')
            ->with($userId)
            ->will($this->returnValue(2013531));
        $this->_loginUser(1);

        $this->get("/users/view/$userId");
        $this->assertResponseCode(200);
        $this->assertNoRedirect();
        $this->assertResponseContains('Ulysses');
        // solves count
        $this->assertResponseContains('2013531');
    }

    /**
     * User doesn't see activation status of unactivated user
     */
    public function testViewUserNotActivatedUser()
    {
        Configure::write('Saito.language', 'bzs');
        $this->_loginUser(3);
        $this->get('/users/view/10');
        $this->assertResponseCode(200);
        $this->assertResponseNotContains('user.actv.t');
        $this->assertResponseNotContains('user.actv.ny');
    }

    /**
     * User doesn't see activation status of unactivated user
     */
    public function testIndexUserNotActivatedUser()
    {
        Configure::write('Saito.language', 'bzs');
        $this->_loginUser(3);
        $this->get('/users/index');
        $this->assertResponseCode(200);
        $this->assertResponseNotContains('user.actv.t');
        $this->assertResponseNotContains('user.actv.ny');
    }

    /**
     * Admin sees activation status of unactivated user
     */
    public function testViewUserNotActivatedAdmin()
    {
        Configure::write('Saito.language', 'bzs');
        $this->_loginUser(1);
        $this->get('/users/view/10');
        $this->assertResponseCode(200);
        $this->assertResponseContains('user.actv.t');
        $this->assertResponseContains('user.actv.ny');
    }

    /**
     * Admin doesn't see activation status for activated user
     */
    public function testViewUserActivatedAdmin()
    {
        Configure::write('Saito.language', 'bzs');
        $this->_loginUser(1);
        $this->get('/users/view/3');
        $this->assertResponseCode(200);
        $this->assertResponseNotContains('user.actv.t');
        $this->assertResponseNotContains('user.actv.ny');
    }

    /**
     * Admin sees activation status of unactivated user
     */
    public function testIndexUserNotActivatedAdmin()
    {
        Configure::write('Saito.language', 'bzs');
        $this->_loginUser(1);
        $this->get('/users/index');
        $this->assertResponseCode(200);
        $this->assertResponseContains('user.actv.ny');
    }

    /**
     * User has messaging disabled. Normal user can't see it
     */
    public function testContactMsgNotAllowed()
    {
        $this->_loginUser(3);
        $userId = 4;

        $this->get('/users/view/' . $userId);

        $this->assertResponseCode(200);
        $this->assertResponseNotContains('<a href="/contacts/user/' . $userId . '">');
    }

    /**
     * User has messaging disabled. Privileged user may see it
     */
    public function testContactMsgNotAllowedButPrivileged()
    {
        $this->_loginUser(1);
        $userId = 4;

        $this->get('/users/view/' . $userId);

        $this->assertResponseCode(200);
        $this->assertResponseContains('<a href="/contacts/user/' . $userId . '">');
    }

    /**
     * User has messaging enabled. Normal user can see it
     */
    public function testContactMsgAllowed()
    {
        $this->_loginUser(3);
        $userId = 9;

        $this->get('/users/view/' . $userId);

        $this->assertResponseCode(200);
        $this->assertResponseContains('<a href="/contacts/user/' . $userId . '">');
    }

    public function testViewSanitation()
    {
        $this->_loginUser(3);
        $this->get('/users/view/7');

        $this->assertResponseContains('&amp;&lt;Username');
        $this->assertResponseNotContains('<&Username');
        $this->assertResponseContains('&amp;&lt;RealName');
        $this->assertResponseContains('&amp;&lt;Homepage');
        $this->assertResponseContains('&amp;&lt;Place');
        $this->assertResponseContains('&amp;&lt;Profile');
        $this->assertResponseContains('&amp;&lt;Signature');
    }

    public function testName()
    {
        $this->_loginUser(3);
        $this->get('/users/name/Mitch');
        $this->assertRedirect('/users/view/2');
    }

    public function testEditViewUserDoesNotExist()
    {
        $this->_loginUser(1);
        $this->expectException(RecordNotFoundException::class);
        $this->get('/users/edit/9999');
    }

    public function testEditNotLoggedIn()
    {
        $url = '/users/edit/3';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testEditNotUsersEntryGet()
    {
        $this->_loginUser(3);
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->get('/users/edit/2');
    }

    public function testEditNotUsersEntryPost()
    {
        $this->_loginUser(3);
        $this->mockSecurity();
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->post('/users/edit/2', ['username' => 'foo']);
    }

    public function testEditNotModEntryGet()
    {
        $this->_loginUser(2);
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->get('/users/edit/3');
    }

    public function testEditNotModEntryPost()
    {
        $this->_loginUser(2);
        $this->mockSecurity();
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->post('/users/edit/3', ['username' => 'foo']);
    }

    public function testEditNotUsersEntryButAdmin()
    {
        $this->_loginUser(1);
        $this->get('/users/edit/3');
        $this->assertResponseOk();

        $this->mockSecurity();
        $this->post('/users/edit/3', ['username' => 'foo']);
        $this->assertRedirect('/users/view/3');
    }

    public function testIndex()
    {
        $url = '/users/index';
        $this->get($url);
        $this->assertRedirectLogin($url);

        $this->_loginUser(1);
        $this->get('/users/index');
        $this->assertResponseOk();
    }

    public function testIgnore()
    {
        $this->mockSecurity();
        $this->_loginUser(3);

        $Ignores = TableRegistry::get('UserIgnores');
        $this->assertEmpty($Ignores->find()->count());

        $this->post('/users/ignore', ['id' => 1]);

        $this->assertEquals(1, $Ignores->find()->count());
        $this->assertEquals(3, $Ignores->find()->first()->get('user_id'));
        $this->assertEquals(1, $Ignores->find()->first()->get('blocked_user_id'));
        $this->assertRedirect();

        $this->post('/users/ignore', ['id' => 1]);
        $this->assertEquals(1, $Ignores->find()->count());

        $this->post('/users/unignore', ['id' => 1]);
        $this->assertEquals(0, $Ignores->find()->count());
    }

    public function testLockFailureNotLoggedIn()
    {
        $this->mockSecurity();

        /* not logged in should'nt be allowed */
        $this->post('/users/lock', ['lockUserId' => 3]);
        $this->assertRedirectContains('/login');
    }

    public function testLockFailureUserDontLockUsers()
    {
        $this->mockSecurity();
        $this->_loginUser(3);

        $this->expectException(SaitoForbiddenException::class);

        $this->post('/users/lock', ['lockUserId' => 4]);
    }

    public function testLockFailure()
    {
        /* setup */
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');

        $this->_loginUser(11);

        // you can't lock yourself out
        $this->post('/users/lock', ['lockUserId' => 11]);
        $user = $Users->findById(11)->first();
        $this->assertFalse($user->get('user_lock'));
    }

    public function testLockFailureNoPermission()
    {
        Configure::read('Saito.Permission.Resources')
            ->get('saito.core.user.lock.set')
            ->disallow((new ResourceAC())->asEverybody());
        $this->mockSecurity();
        $this->_loginUser(11);

        $this->expectException(SaitoForbiddenException::class);

        $this->post('/users/lock', ['lockUserId' => 11]);
    }

    public function testLockAndUnlockSuccess()
    {
        /* setup */
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');

        // mod locks user
        $this->_loginUser(2);

        $count = $Users->UserBlocks->find()->count();

        $this->post('/users/lock', ['lockUserId' => 3]);
        $user = $Users->findById(3)->first();
        $this->assertTrue($user->get('user_lock'));

        $this->post('/users/lock', ['lockUserId' => 4]);
        $user = $Users->findById(4)->first();
        $this->assertTrue($user->get('user_lock'));

        // mod unlocks user in reverse order
        $this->get('/users/unlock/' . ($count + 2));
        $user = $Users->findById(4)->first();
        $this->assertTrue($user->get('user_lock') == false);

        $this->get('/users/unlock/' . ($count + 1));
        $user = $Users->findById(3)->first();
        $this->assertTrue($user->get('user_lock') == false);

        $after = $Users->UserBlocks->find()->count();
        $this->assertEquals($count + 2, $after);
    }

    public function testLockSetUserDoesNotExistFailure()
    {
        $this->mockSecurity();
        $this->_loginUser(2);

        $this->expectException(RecordNotFoundException::class);

        $this->post('/users/lock', ['lockUserId' => 9999]);
    }

    public function testLockResult()
    {
        $this->mockSecurity();
        $this->_loginUser(2);
        $Users = TableRegistry::get('Users');
        $userToLock = 5;

        /// Mod locks user 5
        $this->post('/users/lock', ['lockUserId' => 5]);
        $user = $Users->findById($userToLock)->first();
        $this->assertTrue($user->get('user_lock') == true);
        $this->_logoutUser();

        /// Locked user are thrown out
        $this->_loginUser($userToLock);
        $result = $this->get('/entries/index');
        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNull($this->_controller->request->getSession()->read('Auth'));

        /// Locked user can't relogin
        $this->_logoutUser();
        $this->post(
            '/login',
            ['username' => 'Uma', 'password' => 'test']
        );

        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNull(
            $this->_controller->request->getSession()->read('Auth')
        );
    }

    public function testChangePasswordNotLoggedIn()
    {
        $this->get('/users/changepassword/5');
        $this->assertRedirectContains('/login');
    }

    public function testChangePasswordWrongUser()
    {
        $this->_loginUser(4);
        $data = [
            'password_old' => 'test',
            'password' => 'test_new',
            'password_confirm' => 'test_new',
        ];
        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->post('/users/changepassword/1', $data);
    }

    public function testChangePasswordViewFormWrongUser()
    {
        $this->_loginUser(4);
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->get('/users/changepassword/5');
    }

    public function testChangePasswordViewForm()
    {
        $this->_loginUser(4);
        $this->get('/users/changepassword/4');
        $this->assertNoRedirect();
    }

    public function testChangePasswordOldPasswordNotCorrect()
    {
        $this->mockSecurity();
        $this->_loginUser(4);

        $Users = TableRegistry::get('Users');
        $password = $Users->get(5)->get('password');

        $data = [
            'password_old' => 'test_something',
            'password' => 'test_new_foo',
            'password_confirm' => 'test_new_foo',
        ];
        $this->post('/users/changepassword/4', $data);

        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $this->assertEquals($result->get('password'), $password);

        $this->assertNoRedirect();
    }

    public function testChangePasswordConfirmationFailed()
    {
        $this->_loginUser(5);
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');

        /// Set user password to "test" with current password-hasher
        $user = $Users->get(5);
        $oldPassword = 'test';
        $user->set('password', $oldPassword);
        $user = $Users->save($user);

        $data = [
            'password_old' => $oldPassword,
            'password' => 'test_new_foo',
            'password_confirm' => 'test_new_bar',
        ];
        $this->post('/users/changepassword/5', $data);

        $expected = $user['password'];
        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $this->assertEquals($result->get('password'), $expected);

        $this->assertNoRedirect();
    }

    public function testChangePasswordSuccess()
    {
        $this->_loginUser(5);
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');

        /// Set user password to "test" with current password-hasher
        $user = $Users->get(5);
        $oldPassword = 'test';
        $user->set('password', $oldPassword);
        $user = $Users->save($user);

        $data = [
            'password_old' => $oldPassword,
            'password' => 'test_new',
            'password_confirm' => 'test_new',
        ];
        $this->post('/users/changepassword/5', $data);

        $result = $Users->get(5, ['fields' => 'password']);
        $pwH = new DefaultPasswordHasher();
        $newHash = $result->get('password');
        $this->assertTrue($pwH->check('test_new', $newHash));

        $this->assertRedirect('users/edit/5');
    }

    public function testSetPasswordAnon()
    {
        $this->get('/users/setpassword/4');
        $this->assertRedirectLogin('/users/setpassword/4');
    }

    public function testSetPasswordUser()
    {
        $this->_loginUser(3);
        $this->expectException(SaitoForbiddenException::class);
        $this->get('/users/setpassword/4');
    }

    public function testSetPasswordUserNotFound()
    {
        $this->_loginUser(1);
        $this->expectException(RecordNotFoundException::class);
        $this->get('/users/setpassword/9999');
    }

    public function testSetPasswordGet()
    {
        $this->_loginUser(1);
        $this->get('/users/setpassword/4');
        $this->assertResponseCode(200);
    }

    public function testSetPasswordPostSuccess()
    {
        $this->_loginUser(1);
        $this->mockSecurity();
        $data = [
            'password' => 'test_new',
            'password_confirm' => 'test_new',
        ];
        $this->post('/users/setpassword/5', $data);

        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $pwH = new DefaultPasswordHasher();
        $this->assertTrue($pwH->check('test_new', $result->get('password')));

        $this->assertRedirect('users/edit/5');
    }

    public function testSetPasswordPostFailurePwDontMatch()
    {
        $userId = 1;
        $this->_loginUser($userId);
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');
        $password = $Users->get($userId)->get('password');

        $data = [
            'password' => 'test_new',
            'password_confirm' => 'test_foo',
        ];
        $this->post('/users/setpassword/5', $data);

        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $this->assertEquals($result->get('password'), $password);

        $this->assertNoRedirect();
    }

    /**
     * Checks that the mod-button is in-/visible
     */
    public function testViewModButton()
    {
        /*
         * Mod Button is not visible for anon users
         */
        $this->get('users/view/5');
        $this->assertResponseNotContains('dropdown');

        /*
         * Mod Button is not visible for normal users
         */
        $this->_loginUser(3);
        $this->get('users/view/5');
        $this->assertResponseNotContains('dropdown');

        /*
         * Mod Button is visible for admin
         */
        $this->_loginUser(1);
        $this->get('users/view/5');
        $this->assertResponseContains('dropdown');

        /*
         * Mod Button is currently visible for mod
         */
        $this->_loginUser(1);
        $this->get('users/view/5');
        $this->assertResponseContains('dropdown');
    }

    /**
     * Mod menu is currently empty for mod
     */
    public function testViewBlockButtonEmpty()
    {
        $this->_loginUser(3);
        $this->get('users/view/5');
        $this->assertResponseNotContains('dropdown');
    }

    public function testViewBlockButtonBlockUiTrue()
    {
        $this->_loginUser(2);
        $this->get('users/view/5');
        $result = (string)$this->_response->getBody();
        $this->assertXPath($result, '//input[@value=5][@name="lockUserId"]');
    }

    public function testAvatarGetNotLoggedInFailure()
    {
        $url = '/users/avatar/3';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testAvatarUserNotFound()
    {
        $this->_loginUser(1);
        $this->expectException(RecordNotFoundException::class);
        $this->get('/users/avatar/9999');
    }

    public function testAvatarGetSuccess()
    {
        $this->_loginUser(3);
        $this->get('/users/avatar/3');
        $this->assertResponseOk();
    }

    public function testAvatarPostNotLoggedInFailure()
    {
        $url = '/users/avatar/3';
        $this->post($url);
        $this->assertRedirectLogin($url);
    }

    public function testAvatarPostNotOwnUserFailure()
    {
        $this->_loginUser(3);
        $this->mockSecurity();
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->post('/users/avatar/9');
    }

    public function testAvatarPostNewPicture()
    {
        $userId = 3;
        $root = Configure::read('Saito.Settings.uploadDirectory');
        $directory = new Folder("{$root}/users/avatar/{$userId}/");
        $directory->delete();
        $this->_loginUser($userId);
        $this->mockSecurity();

        $im = imagecreate(100, 100);
        imagecolorallocate($im, 255, 0, 0);
        ob_start();
        imagepng($im);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        $testFiles = ['test1.png', 'test2.png'];
        // run twice to test avatar deletion on second try
        foreach ($testFiles as $testFile) {
            $testFile = TMP . $testFile;

            file_put_contents($testFile, $imageData);

            $data = [
                'avatar' => [
                    'tmp_name' => $testFile,
                    'error' => 0,
                    'name' => 'test.png',
                    'type' => 'image/png',
                    'size' => strlen($imageData),
                ],
                'avatarDelete' => null,
            ];

            $this->post('/users/avatar/3', $data);

            if (file_exists($testFile)) {
                unlink($testFile);
            }

            $user = $this->_controller->Users->get($userId);

            $dir = $user->get('avatar_dir');
            $this->assertEquals($userId, $dir);

            $filename = $user->get('avatar');
            $this->assertNotEmpty($filename);

            $fullDir = "{$root}/users/avatar/{$dir}/";
            $this->assertFileExists($fullDir . $filename);
            $this->assertFileExists($fullDir . "square_{$filename}");

            $d = new Folder($fullDir);
            $dc = $d->find('[^.].*');
            $this->assertCount(2, $dc);
        }

        $data = [
            'avatar' => [],
            'avatarDelete' => 1,
        ];
        $this->post('/users/avatar/3', $data);

        $dc = $d->find('[^.].*');
        $this->assertCount(0, $dc);

        $directory->delete();
    }

    public function testAvatarPostPictureToLargeFailure()
    {
        $userId = 3;
        $root = Configure::read('Saito.Settings.uploadDirectory');
        $directory = new Folder("{$root}/users/avatar/{$userId}/");
        $directory->delete();
        $this->_loginUser($userId);
        $this->mockSecurity();

        $im = imagecreate(100, 1501);
        imagecolorallocate($im, 255, 0, 0);
        ob_start();
        imagepng($im);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        $testFile = TMP . 'test.png';
        file_put_contents($testFile, $imageData);

        $data = [
            'avatar' => [
                'tmp_name' => $testFile,
                'error' => 0,
                'name' => 'test.png',
                'type' => 'image/png',
            ],
            'avatarDelete' => null,
        ];

        $this->post('/users/avatar/3', $data);
        $errors = $this->viewVariable('user')->getErrors();
        $this->assertArrayHasKey('avatar-dimension', $errors['avatar']);
        $this->assertResponseOk();
    }

    public function testAvatarPostPictureToSmall()
    {
        $userId = 3;
        $root = Configure::read('Saito.Settings.uploadDirectory');
        $directory = new Folder("{$root}/users/avatar/{$userId}/");
        $directory->delete();
        $this->_loginUser($userId);
        $this->mockSecurity();

        $im = imagecreate(99, 100);
        imagecolorallocate($im, 255, 0, 0);
        ob_start();
        imagepng($im);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        $testFile = TMP . 'test.png';
        file_put_contents($testFile, $imageData);

        $data = [
            'avatar' => [
                'tmp_name' => $testFile,
                'error' => 0,
                'name' => 'test.png',
                'type' => 'image/png',
            ],
            'avatarDelete' => null,
        ];

        $this->post('/users/avatar/3', $data);
        $errors = $this->viewVariable('user')->getErrors();
        $this->assertArrayHasKey('avatar-dimension', $errors['avatar']);
        $this->assertResponseOk();
    }

    public function testAvatarPostPictureWrongExt()
    {
        $userId = 3;
        $root = Configure::read('Saito.Settings.uploadDirectory');
        $directory = new Folder("{$root}/users/avatar/{$userId}/");
        $directory->delete();
        $this->_loginUser($userId);
        $this->mockSecurity();

        $im = imagecreate(800, 800);
        imagecolorallocate($im, 255, 0, 0);
        ob_start();
        imagepng($im);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        $testFile = TMP . 'test.jpg';
        file_put_contents($testFile, $imageData);

        $data = [
            'avatar' => [
                'tmp_name' => $testFile,
                'error' => 0,
                'name' => 'test.mp4',
                'type' => 'image/png',
            ],
            'avatarDelete' => null,
        ];

        $this->post('/users/avatar/3', $data);
        $errors = $this->viewVariable('user')->getErrors();
        $this->assertArrayHasKey('avatar-extension', $errors['avatar']);
        $this->assertResponseOk();
    }

    public function testLogoutSuccess()
    {
        $this->_loginUser(1);
        $this->cookie('my_cookie', 'foo');

        $user = $this->get('/logout');

        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());

        $cookies = $this->_response->getCookieCollection();
        $cookie = $cookies->get('my_cookie');
        $this->assertTrue($cookie->isExpired());
        $this->assertSame($this->_controller->request->getAttribute('webroot'), $cookie->getPath());

        $this->assertRedirect('/');
    }

    public function testRoleViewUserUnauthenticated()
    {
        $url = '/users/role/3';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testRoleUserViewDoesntExist()
    {
        $this->_loginUser(1);
        $this->expectException(RecordNotFoundException::class);
        $this->get('/users/role/9999');
    }

    public function testRoleViewUnauthorized()
    {
        $this->_loginUser(3);
        $this->expectException(SaitoForbiddenException::class);
        $this->get('/users/role/3');
    }

    public function testRoleViewSuccessRestricted()
    {
        $this->_loginUser(1);
        $this->get('/users/role/3');
        $this->assertResponseCode(200);

        $this->assertResponseNotContains('user-type-anon');
        $this->assertResponseNotContains('user-type-owner');
        $this->assertResponseNotContains('user-type-admin');
        $this->assertResponseContains('user-type-user');
        $this->assertResponseContains('user-type-mod');
    }

    public function testRoleViewSuccessUnRestricted()
    {
        $this->_loginUser(11);
        $this->get('/users/role/3');
        $this->assertResponseCode(200);

        $this->assertResponseNotContains('user-type-anon');
        $this->assertResponseContains('user-type-owner');
        $this->assertResponseContains('user-type-admin');
        $this->assertResponseContains('user-type-user');
        $this->assertResponseContains('user-type-mod');
    }

    public function testRolePostTypeNotAllowed()
    {
        $this->setI18n('bsz');
        $this->_loginUser(1);
        $this->mockSecurity();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1573376871);
        $data = ['user_type' => 'foo'];
        $this->put('/users/role/3', $data);

        /*
        $this->assertResponseContains('vld.user.user_type.allowedType');
        $this->assertEquals('user', $this->_controller->Users->get(3)->get('user_type'));
        */
    }

    public function testRolePostSuccess()
    {
        $this->_loginUser(11);
        $this->mockSecurity();
        $userId = 3;
        $newType = 'admin';

        $data = ['user_type' => $newType];
        $this->put("/users/role/$userId", $data);

        $this->assertRedirect('/users/edit/3');

        $Users = TableRegistry::getTableLocator()->get('Users');
        $this->assertTrue($Users->get($userId)->getRole() === $newType);
    }

    public function testRolePostUnauthorized()
    {
        $this->_loginUser(2);
        $this->mockSecurity();
        $userId = 3;
        $newType = 'admin';

        $this->expectException(SaitoForbiddenException::class);

        $data = ['user_type' => $newType];
        $this->put("/users/role/$userId", $data);
    }

    public function testDeleteNotAuthenticatedCantDelete()
    {
        $this->mockSecurity();
        $url = '/users/delete/3';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    public function testDeleteNoPermission()
    {
        Configure::read('Saito.Permission.Resources')
            ->get('saito.core.user.delete')
            ->disallow((new ResourceAC())->asEverybody());
        $this->mockSecurity();
        $this->_loginUser(11);

        $this->expectException(SaitoForbiddenException::class);

        $url = '/users/delete/4';
        $this->get($url);
    }

    public function testUserDoesNotExist()
    {
        $this->mockSecurity();
        $this->_loginUser(1);

        $this->expectException(RecordNotFoundException::class);
        $this->post('/users/delete/9999');
    }

    public function testDeleteFailureNoConfirmation()
    {
        $this->setI18n('bzs');
        $this->mockSecurity();
        $this->_loginUser(1);
        $userToDelete = 3;

        $this->post('/users/delete/' . $userToDelete);

        $this->assertTrue($this->_controller->Users->exists($userToDelete));
        $this->assertFlash('user.del.fail.3', 'error');
    }

    public function testDeleteYourselfFailure()
    {
        $this->setI18n('bzs');
        $this->mockSecurity();
        $userToDelete = 11;
        $this->_loginUser($userToDelete);

        $data = ['userdeleteconfirm' => 1];
        $this->post('/users/delete/' . $userToDelete, $data);

        $this->assertTrue($this->_controller->Users->exists($userToDelete));
        $this->assertFlash('user.del.fail.1', 'error');
    }

    public function testDeleteSuccess()
    {
        $this->mockSecurity();
        $this->_loginUser(6);
        $data = ['userdeleteconfirm' => 1];

        $this->post('/users/delete/5', $data);

        $this->assertFalse($this->_controller->Users->exists(5));
        $this->assertRedirect('/');
    }
}
