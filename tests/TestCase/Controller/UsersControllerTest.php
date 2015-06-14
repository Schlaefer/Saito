<?php

namespace App\Test\TestCase\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Email\Email;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Saito\Test\IntegrationTestCase;

class UsersControllerTestCase extends IntegrationTestCase
{

    public $fixtures = [
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
        'app.user_read',
        'plugin.bookmarks.bookmark'
    ];

    const MAPQUEST = 'mapquestapi.com/sdk';

    public function testAdminAddSuccess()
    {
        $this->mockSecurity();
        $data = [
            'username' => 'foo',
            'user_email' => 'fo3@example.com',
            'user_password' => 'test',
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
        $this->post('/admin/users/add');
        $this->assertRedirect('/login');
    }

    public function testLogin()
    {
        $data = ['username' => 'Ulysses', 'password' => 'test'];

        $this->get('/');
        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNull(
            $this->_controller->request->Session()->read('Auth.User')
        );

        $this->mockSecurity();
        $this->post('/users/login', $data);
        $this->assertTrue($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNotNull(
            $this->_controller->request->Session()->read('Auth.User')
        );

        //# successful login redirects
        $this->assertRedirect('/');

        //# last login time should be set
        $Users = TableRegistry::get('Users');
        $user = $Users->get(3, ['fields' => 'last_login']);
        $this->assertWithinRange(time($user->get('last_login')), time(), 1);
    }

    public function testLoginShowForm()
    {
        //# show login form
        $this->get('/users/login');
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
                    'type' => 'text'
                ]
            ],
            'input#password' => [
                'attributes' => [
                    'autocomplete' => 'current-password',
                    'name' => 'password',
                    'required' => 'required',
                    'tabindex' => '101',
                    'type' => 'password'
                ]
            ]
        ];
        $this->assertContainsTag($username, $this->_response->body());

        //# test logout on form show
        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $user = $this->_loginUser(3);
        $this->_controller->CurrentUser->setSettings($user);
        $this->assertTrue($this->_controller->CurrentUser->isLoggedIn());

        $this->get('/users/login');
        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
    }

    public function testLoginUserNotActivated()
    {
        $this->mockSecurity();
        $data = ['username' => 'Diane', 'password' => 'test'];
        $result = $this->post('/users/login', $data);
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
        $this->post('/users/login', $data);
        $this->assertResponseContains('is locked.');
    }

    public function testLogout()
    {
        $this->_loginUser(3);
        $this->get('/users/logout');
        $tags = [
            'meta[http-equiv="refresh"]' => [
                'attributes' => [
                    'http-equiv' => 'refresh',
                    'content' => '1; '
                ]
            ]
        ];
        $this->assertResponseContainsTags($tags);
    }

    public function testRegisterEmailFailed()
    {
        $this->mockSecurity();

        $transporter = $this->mockMailTransporter();
        $transporter
            ->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Exception));

        Configure::write('Saito.Settings.tos_enabled', false);
        $data = array(
            'username' => 'NewUser1',
            'user_email' => 'NewUser1@example.com',
            'user_password' => 'NewUser1spassword',
            'password_confirm' => 'NewUser1spassword',
        );

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
                'activate_code >' => 0
            ]
        );
        $this->assertTrue($exists);
    }

    public function testRegisterViewForm()
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
                    'type' => 'text'
                ]
            ],
            'input#user-email' => [
                'attributes' => [
                    'autocomplete' => 'email',
                    'name' => 'user_email',
                    'required' => 'required',
                    'tabindex' => '2',
                    'type' => 'text'
                ]
            ],
            'input#user-password' => [
                'attributes' => [
                    'autocomplete' => 'new-password',
                    'name' => 'user_password',
                    'tabindex' => '3',
                    'type' => 'password'
                ]
            ],
            'input#password-confirm' => [
                'attributes' => [
                    'autocomplete' => 'new-password',
                    'name' => 'password_confirm',
                    'tabindex' => '4',
                    'type' => 'password'
                ]
            ]
        ];
        $this->assertContainsTag($expected, $this->_response->body());
    }

    public function testRegisterCheckboxNotOnPage()
    {
        Configure::write('Saito.Settings.tos_enabled', false);
        $this->get('users/register');

        $this->assertResponseOk();
        $this->assertResponseNotContains('tos_confirm');
        $this->assertResponseNotContains('http://example.com/tos-url.html/');
        $this->assertResponseNotContains('disabled');
    }

    public function testRegisterCheckboxOnPage()
    {
        $this->get('users/register');
        $this->assertResponseContains('tos_confirm');
        $this->assertResponseContains('http://example.com/tos-url.html/');
        $this->assertResponseContains('disabled');
    }

    public function testRegisterCheckboxOnPageCustomTosUrl()
    {
        Configure::write('Saito.Settings.tos_url', '');
        $this->get('users/register');
        $this->assertResponseContains(
            $this->_controller->request->webroot . 'pages/en/tos'
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
            'user_password' => 'NewUser1spassword',
            'password_confirm' => 'NewUser1spassword',
            'tos_confirm' => '0'
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
                            $email->from(),
                            ['register@example.com' => 'macnemo']
                        );
                        $this->assertEquals(
                            $email->to(),
                            ['NewUser1@example.com' => 'NewUser1']
                        );

                        $user = $Users->find()
                            ->where(['username' => 'NewUser1'])
                            ->first();
                        $id = $user->get('id');
                        $activate = $user->get('activate_code');
                        $this->assertContains(
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
                'activate_code >' => 0
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
            'user_password' => 'NewUser1spassword',
            'password_confirm' => 'NewUser1spassword',
        ];
        $this->post('users/register', $data);

        $exists = $Users->exists(
            [
                'username' => 'NewUser1',
                'activate_code >' => 0
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

        $data = array(
            'username' => "mITch",
            'user_email' => 'alice@example.com',
            'user_password' => 'NewUserspassword',
            'password_confirm' => 'NewUser1spassword',
        );

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
        $this->get('/users/setcategory/all');
        $this->assertRedirect('/login');
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

                $controller = $event->subject();
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

                $controller = $event->subject();
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
            ]
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

                $controller = $event->subject();
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

        $validData = ['slidetab_userlist', 'slidetab_shoutbox'];
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

    public function testSlidetabToggleSuccess()
    {
        $this->_loginUser(3);
        $data = ['slidetabKey' => 'show_userlist'];

        $Users = TableRegistry::get('Users');
        $user = $Users->get(3);
        $this->assertFalse($user->get('show_userlist'));

        $this->_setAjax();
        $this->post('/users/slidetabToggle', $data);

        $user = $Users->get(3);
        $this->assertTrue($user->get('show_userlist'));
    }

    public function testSlidetabToggleFailure()
    {
        $this->_loginUser(3);
        $data = ['slidetabKey' => 'show_foo'];
        $this->setExpectedException(
            '\Cake\Network\Exception\BadRequestException'
        );
        $this->_setAjax();
        $this->post('/users/slidetabToggle', [$data]);
    }

    public function testViewProfileRequestByUsername()
    {
        $this->_loginUser(3);
        $this->get('/users/view/Mitch');
        $this->assertRedirect('/users/name/Mitch');
    }

    public function testViewProfileForbiddenForAnon()
    {
        $this->get('/users/view/1');
        $this->assertRedirect('/login');
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

    public function testMapLinkInMenu()
    {
        $this->_loginUser(3);

        // not enabled, no link
        Configure::write('Saito.Settings.map_enabled', 0);
        $this->get('/users/view/2');
        $this->assertResponseNotContains('/users/map');
        $this->get('/users/index');
        $this->assertResponseNotContains('/users/map');

        // enabled, link
        Configure::write('Saito.Settings.map_enabled', 1);
        $this->get('/users/view/2');
        $this->assertResponseContains('/users/map');
        $this->get('/users/index');
        $this->assertResponseContains('/users/map');
    }

    public function testMapDisabled()
    {
        $this->_loginUser(3);
        $this->get('/users/edit/3');
        $this->assertResponseNotContains('class="saito-usermap"');
        $this->assertResponseNotContains(static::MAPQUEST);

        $this->get('/users/view/3');
        $this->assertResponseNotContains('class="saito-usermap"');
        $this->assertResponseNotContains(static::MAPQUEST);

        $this->get('/users/map');
        $this->assertResponseNotContains('class="saito-usermap"');
        $this->assertRedirect('/');
    }

    public function testMapActivated()
    {
        Configure::write('Saito.Settings.map_enabled', 1);

        $this->_loginUser(3);
        $this->get('/users/edit/3');
        $this->assertResponseContains('class="saito-usermap"');
        $this->assertResponseContains(static::MAPQUEST);

        $this->get('/users/view/2');
        $this->assertResponseNotContains('class="saito-usermap"');
        $this->get('/users/view/3');
        $this->assertResponseContains('class="saito-usermap"');

        $this->get('/users/map');
        $this->assertResponseContains('class="saito-usermap"');

        // Map CSS and JS should only be included on page if necessary
        $this->get('/users/index');
        $this->assertResponseNotContains(static::MAPQUEST);
    }

    public function testMapsNotLoggedIn()
    {
        $this->get('/users/map');
        $this->assertRedirect('/login');
    }

    public function testName()
    {
        $this->_loginUser(3);
        $this->get('/users/name/Mitch');
        $this->assertRedirect('/users/view/2');
    }

    public function testEditNotLoggedIn()
    {
        $this->get('/users/edit/3');
        $this->assertRedirect('/login');
    }

    public function testEditNotUsersEntryGet()
    {
        $this->_loginUser(3);
        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
        $this->get('/users/edit/2');
    }

    public function testEditNotUsersEntryPost()
    {
        $this->_loginUser(3);
        $this->mockSecurity();
        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
        $this->post('/users/edit/2', ['username' => 'foo']);
    }

    public function testEditNotModEntryGet()
    {
        $this->_loginUser(2);
        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
        $this->get('/users/edit/3');
    }

    public function testEditNotModEntryPost()
    {
        $this->_loginUser(2);
        $this->mockSecurity();
        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
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
        $this->get('/users/index');
        $this->assertRedirect('/login');

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

    public function testLock()
    {
        /* setup */
        $this->mockSecurity();
        $Users = TableRegistry::get('Users');

        /* not logged in should'nt be allowed */
        $this->post('/users/lock', ['lockUserId' => 3]);
        $this->assertRedirect('/login');

        // user can't lock other users
        $this->_loginUser(3);
        try {
            $this->post('/users/lock', ['lockUserId' => 4]);
        } catch (BadRequestException $e) {
        }
        $user = $Users->findById(4)->first();
        $this->assertFalse($user->get('user_lock'));

        // mod locks user
        $this->_loginUser(2);
        $this->post('/users/lock', ['lockUserId' => 4]);
        $user = $Users->findById(4)->first();
        $this->assertTrue($user->get('user_lock') == true);

        // mod unlocks user
        $this->post('/users/lock', ['lockUserId' => 4]);
        $user = $Users->findById(4)->first();
        $this->assertTrue($user->get('user_lock') == false);

        // you can't lock yourself out
        $this->post('/users/lock', ['lockUserId' => 2]);
        $user = $Users->findById(2)->first();
        $this->assertTrue($user->get('user_lock') == false);

        // mod can't lock admin
        $this->post('/users/lock', ['lockUserId' => 1]);
        $user = $Users->findById(1)->first();
        $this->assertTrue($user->get('user_lock') == false);

        // user does not exit
        $this->post('/users/lock', ['lockUserId' => 9999]);
        $this->assertRedirect('/');

        // locked user are thrown out
        $this->post('/users/lock', ['lockUserId' => 5]);
        $user = $Users->findById(5)->first();
        $this->assertTrue($user->get('user_lock') == true);
        $this->_logoutUser();

        $this->_loginUser(5);
        $this->get('/entries/index');
        $this->assertRedirect('users/logout');

        // locked user can't relogin
        $this->assertTrue($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNotNull(
            $this->_controller->request->Session()->read('Auth.User')
        );

        $this->_logoutUser();
        $this->post(
            '/users/login',
            ['username' => 'Uma', 'password' => 'test']
        );

        $this->assertFalse($this->_controller->CurrentUser->isLoggedIn());
        $this->assertNull(
            $this->_controller->request->Session()->read('Auth.User')
        );
    }

    public function testChangePasswordNotLoggedIn()
    {
        $this->get('/users/changepassword/5');
        $this->assertRedirect('login');
    }

    public function testChangePasswordWrongUser()
    {
        $this->_loginUser(4);
        $data = [
            'password_old' => 'test',
            'user_password' => 'test_new',
            'password_confirm' => 'test_new',
        ];
        $this->setExpectedException(
            'Cake\Network\Exception\BadRequestException'
        );
        $this->post('/users/changepassword/1', $data);
    }

    public function testChangePasswordViewFormWrongUser()
    {
        $this->_loginUser(4);
        $this->setExpectedException('Saito\Exception\SaitoForbiddenException');
        $this->get('/users/changepassword/5');
    }

    public function testChangePasswordViewForm()
    {
        $this->_loginUser(4);
        $this->get('/users/changepassword/4');
        $this->assertNoRedirect();
    }

    public function testChangePasswordConfirmationFailed()
    {
        $this->_loginUser(4);
        $this->mockSecurity();

        $data = [
            'password_old' => 'test',
            'user_password' => 'test_new_foo',
            'password_confirm' => 'test_new_bar'
        ];
        $this->post('/users/changepassword/4', $data);

        $expected = '098f6bcd4621d373cade4e832627b4f6';
        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $this->assertEquals($result->get('password'), $expected);

        $this->assertNoRedirect();
    }

    public function testChangePasswordOldPasswordNotCorrect()
    {
        $this->mockSecurity();
        $this->_loginUser(4);

        $data = [
            'password_old' => 'test_something',
            'user_password' => 'test_new_foo',
            'password_confirm' => 'test_new_foo',
        ];
        $this->post('/users/changepassword/4', $data);

        $expected = '098f6bcd4621d373cade4e832627b4f6';
        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $this->assertEquals($result->get('password'), $expected);

        $this->assertNoRedirect();
    }

    public function testChangePasswordSuccess()
    {
        $this->_loginUser(5);
        $this->mockSecurity();
        $data = [
            'password_old' => 'test',
            'user_password' => 'test_new',
            'password_confirm' => 'test_new',
        ];
        $this->post('/users/changepassword/5', $data);

        $user = TableRegistry::get('Users');
        $result = $user->get(5, ['fields' => 'password']);
        $pwH = new DefaultPasswordHasher();
        $this->assertTrue($pwH->check('test_new', $result->get('password')));

        $this->assertRedirect('users/edit/5');
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
    public function testViewModButtonEmpty()
    {
        Configure::write('Saito.Settings.block_user_ui', false);
        $this->_loginUser(2);
        $this->get('users/view/5');
        $this->assertResponseNotContains('dropdown');
    }

    public function testViewModButtonBlockUiTrue()
    {
        Configure::write('Saito.Settings.block_user_ui', true);
        $this->_loginUser(2);
        $this->get('users/view/5');
        $result = $this->_response->body();
        $this->assertXPath($result, '//input[@value=5][@name="lockUserId"]');
    }

    public function testViewModButtonBlockUiFalse()
    {
        Configure::write('Saito.Settings.block_user_ui', false);
        $this->_loginUser(2);
        $this->get('users/view/5');
        $this->assertResponseNotContains('users/lock/5');
    }
}
