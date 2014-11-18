<?php

namespace Saito\Test;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Saito\User;

abstract class SaitoTestCase extends TestCase
{

    use AssertTrait;
    use TestCaseTrait;

    public function setUp()
    {
        $this->setUpSaito();
    }

    public function tearDown()
    {
        $this->tearDownSaito();
    }

}
