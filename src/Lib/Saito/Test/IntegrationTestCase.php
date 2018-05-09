<?php

namespace Saito\Test;

use App\Test\Fixture\UserFixture;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
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
     * @return \Cake\TestSuite\Model
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
}
