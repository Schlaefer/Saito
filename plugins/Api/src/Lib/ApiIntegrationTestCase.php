<?php

namespace Api\Lib;

use Cake\Core\Configure;
use Saito\Test\IntegrationTestCase;

class ApiIntegrationTestCase extends IntegrationTestCase {

    public function setUp() {
        Configure::write('Saito.Settings.api_enabled', '1');
        parent::setUp();
    }

    protected function _checkDisallowedRequestType($types, $url) {
        foreach ($types as $requestType) {
            try {
                $this->{$requestType}($url);
            } catch (Exception $exception) {
                $this->assertEqual(
                    get_class($exception), 'Saito\Api\UnknownRouteException'
                );
            }
        }
    }

}
