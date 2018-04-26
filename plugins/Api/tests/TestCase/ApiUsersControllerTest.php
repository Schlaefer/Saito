<?php

namespace Api\Test;

use Api\Lib\ApiIntegrationTestCase;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\User\SaitoUser;

/**
 * ApiUsersController Test Case
 *
 */
class ApiUsersControllerTest extends ApiIntegrationTestCase
{

    protected $_apiRoot = 'api/v1/';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.smiley',
        'app.smiley_code',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_read',
        'app.user_online',
        'plugin.bookmarks.bookmark'
    ];

    public function testLoginNoUsername()
    {
        $this->expectException(
            'Cake\Http\Exception\BadRequestException',
            null,
            1433238401
        );
        $this->post($this->_apiRoot . 'login');
    }

    public function testLoginNoPassword()
    {
        $this->expectException(
            'Cake\Http\Exception\BadRequestException',
            null,
            1433238501
        );
        $data = ['username' => 'Jane'];
        $this->post($this->_apiRoot . 'login', $data);
    }

    public function testLoginSuccess()
    {
        $data = [
            'username' => 'Alice',
            'password' => 'test',
            'remember_me' => '1'
        ];

        $expected = json_decode(
            '
				{
					"user": {
						"isLoggedIn": true,
						"id": 1,
						"username": "Alice",
						"last_refresh": null,
						"threads_order": "answer"
					}
				}
			'
        );

        $this->post($this->_apiRoot . 'login.json', $data);
        $this->assertEquals($expected, json_decode((string)$this->_response->getBody()));
    }

    public function testLoginFailure()
    {
        $data = [
            'username' => 'Jane',
            'password' => 'N7',
            'remember_me' => '1'
        ];

        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->post($this->_apiRoot . 'login', $data);
    }

    public function testLoginDisallowedRequestType()
    {
        $this->_checkDisallowedRequestType(
            ['GET', 'PUT', 'DELETE'],
            $this->_apiRoot . 'login'
        );
    }

    public function testLogoutSuccess()
    {
        $this->_loginUser(1);
        $this->post($this->_apiRoot . 'logout.json', ['id' => 1]);
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($result['user']['isLoggedIn']);
    }

    public function testMarkAsReadMissingUserId()
    {
        $this->_loginUser(3);
        $data = [];
        $this->expectException(
            'Cake\Http\Exception\BadRequestException',
            'User id is missing.'
        );
        $this->post($this->_apiRoot . 'markasread', $data);
    }

    public function testMarkAsReadUserIdNotAuthorized()
    {
        $this->_loginUser(3);
        $data = ['id' => 1];
        $this->expectException('Saito\Exception\SaitoForbiddenException');
        $this->post($this->_apiRoot . 'markasread', $data);
    }

    public function testMarkAsReadSuccessNow()
    {
        $userId = 3;
        $this->_loginUser($userId);
        $data = ['id' => $userId];

        $this->post($this->_apiRoot . 'markasread.json', $data);
        $result = (string)$this->_response->getBody();

        $result = json_decode($result, true);
        $this->assertTrue(isset($result['last_refresh']));
        $this->assertWithinRange(
            strtotime($result['last_refresh']),
            time(),
            2
        );
    }

    public function testMarkAsReadSuccessTimestamp()
    {
        $userId = 3;
        $this->_loginUser($userId);
        $data = [
            'id' => $userId,
            'last_refresh' => '2013-07-04T19:53:14+00:00'
        ];

        $this->post($this->_apiRoot . 'markasread.json', $data);
        $result = (string)$this->_response->getBody();
        $result = json_decode($result, true);
        $this->assertTrue(isset($result['last_refresh']));

        $Users = TableRegistry::get('Users');
        $result = $Users->get($userId)->get('last_refresh')->toDateTimeString();
        $expected = '2013-07-04 19:53:14';
        $this->assertEquals($expected, $result);
    }

    /**
     * Send timestamp is ignored is not set if it's older than the current
     * one
     */
    public function testMarkAsReadNoPastValues()
    {
        $userId = 3;

        $Users = TableRegistry::get('Users');
        $user = $Users->get($userId);
        $user->set('last_refresh', '2013-07-04 19:53:14');
        $Users->save($user);

        $this->_loginUser($userId);

        $data = [
            'id' => $userId,
            'last_refresh' => '2013-07-04T19:53:13+00:00'
        ];

        $this->post($this->_apiRoot . 'markasread.json', $data);
        $result = (string)$this->_response->getBody();
        $result = json_decode($result, true);
        $expected = [
                'last_refresh' => '2013-07-04T19:53:14+00:00'
            ] + $data;
        $this->assertEquals($expected, $result);
    }

    public function testMarkAsReadOnlyAuthenticatedUsers()
    {
        $this->post($this->_apiRoot . 'markasread.json');
        $this->assertRedirect('/login');
    }

    public function testMarkAsReadDisallowedRequestType()
    {
        $this->_checkDisallowedRequestType(
            ['GET', 'PUT', 'DELETE'],
            $this->_apiRoot . 'markasread'
        );
    }
}
