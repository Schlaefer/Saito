<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2012-2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use App\Test\Fixture\UserFixture;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase as CakeIntegrationTestCase;

abstract class IntegrationTestCase extends CakeIntegrationTestCase
{
    use AssertTrait;
    use SecurityMockTrait;
    use TestCaseTrait {
        getMockForTable as getMockForTableParent;
    }

    /**
     * @var array cache environment variables
     */
    protected $_env = [];

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->disableErrorHandlerMiddleware();
        $this->setUpSaito();
        $this->_clearCaches();
        $this->markUpdated();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->tearDownSaito();
        $this->_unsetAjax();
        $this->_unsetJson();
        parent::tearDown();
        $this->_clearCaches();
    }

    /**
     * set request ajax
     *
     * @return void
     */
    protected function _setAjax()
    {
        $this->disableCsrf();
        $_ENV['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }

    /**
     * unset request ajax
     *
     * @return void
     */
    protected function _unsetAjax()
    {
        unset($_ENV['HTTP_X_REQUESTED_WITH']);
    }

    /**
     * set request json
     *
     * @return void
     */
    protected function _setJson()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
    }

    /**
     * unset request json
     *
     * @return void
     */
    protected function _unsetJson()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'text/html,application/xhtml+xml,application/xml']
        ]);
    }

    /**
     * Set user agent
     *
     * @param string $agent agent
     * @return void
     */
    protected function _setUserAgent($agent)
    {
        if (isset($this->_env['HTTP_USER_AGENT'])) {
            $this->_env['HTTP_USER_AGENT'] = $_ENV['HTTP_USER_AGENT'];
        }
        $_ENV['HTTP_USER_AGENT'] = $agent;
    }

    /**
     * Mocks a table with methods
     *
     * @param string $table table-name
     * @param array $methods methods to mock
     * @return mixed
     */
    public function getMockForTable($table, array $methods = [])
    {
        $Mock = $this->getMockForTableParent($table, $methods);
        EventManager::instance()->on(
            'Controller.initialize',
            function (Event $event) use ($table, $Mock) {
                $Controller = $event->getSubject();
                $Controller->{$table} = $Mock;
            }
        );

        return $Mock;
    }

    /**
     * Configure next request as user authenticated with JWT-Token
     *
     * @param int $userId user
     * @return void
     */
    protected function loginJwt(int $userId)
    {
        $jwtKey = Configure::read('Security.cookieSalt');
        $jwtPayload = ['sub' => $userId];
        $jwtToken = \Firebase\JWT\JWT::encode($jwtPayload, $jwtKey);

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'bearer ' . $jwtToken,
            ]
        ]);
    }

    /**
     * Login user
     *
     * @param int $id user-ID
     * @return mixed
     */
    protected function _loginUser($id)
    {
        // see: http://stackoverflow.com/a/10411128/1372085
        $this->_logoutUser();
        $userFixture = new UserFixture();
        $users = $userFixture->records;
        $user = $users[$id - 1];
        $this->session(['Auth.User' => $user]);

        return $user;
    }

    /**
     * Logout user
     *
     * @return void
     */
    protected function _logoutUser()
    {
        // if user is logged-in it should interfere with test runs
        if (isset($_COOKIE['Saito-AU'])) :
            unset($_COOKIE['Saito-AU']);
        endif;
        if (isset($_COOKIE['Saito'])) :
            unset($_COOKIE['Saito']);
        endif;
        unset($this->_session['Auth.User']);
    }

    /**
     * {@inheritdoc}
     */
    protected function _sendRequest($url, $method, $data = [])
    {
        // Workaround for Cake 3.6 Router on test bug with named routes in plugins
        // "A route named "<foo>" has already been connected to "<bar>".
        Router::reload();
        parent::_sendRequest($url, $method, $data);
    }

    /**
     * Skip test on particular datasource
     *
     * @param string $datasource MySQL|Postgres
     * @return void
     */
    protected function skipOnDataSource(string $datasource): void
    {
        $datasource = strtolower($datasource);

        $driver = TableRegistry::get('Entries')->getConnection()->getDriver();
        $class = strtolower(get_class($driver));

        if (strpos($class, $datasource)) {
            $this->markTestSkipped("Skipped on datasource '$datasource'");
        }
    }

    /**
     * Marks the Saito installation installed and updated (don't run installer or updater)
     *
     * @return void
     */
    private function markUpdated()
    {
        Configure::write('Saito.installed', true);
        Configure::write('Saito.updated', true);
    }
}
