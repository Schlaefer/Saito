<?php

namespace Api\Lib;

use Cake\Core\Configure;
use Saito\Test\IntegrationTestCase;

abstract class ApiIntegrationTestCase extends IntegrationTestCase
{

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Configure::write('Saito.Settings.api_enabled', '1');
    }

    /**
     * {@inheritDoc}
     */
    public function post($url, $data = [])
    {
        $this->disableCsrf();
        parent::post($url, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function put($url, $data = [])
    {
        $this->disableCsrf();
        parent::put($url, $data);
    }

    /**
     * Check that request types are not allowed
     *
     * @param array $types request types 'GET', 'PUT', …
     * @param string $url URL to test
     * @return void
     */
    protected function _checkDisallowedRequestType(array $types, $url)
    {
        foreach ($types as $requestType) {
            try {
                $this->{$requestType}($url);
            } catch (\Exception $exception) {
                $this->assertEquals(
                    'Api\Error\Exception\UnknownRouteException',
                    get_class($exception)
                );
            }
        }
    }
}
