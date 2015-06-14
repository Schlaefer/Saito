<?php

namespace Saito\Test;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Saito\User;

abstract class SaitoTestCase extends TestCase
{

    use AssertTrait;
    use TestCaseTrait;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->setUpSaito();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->tearDownSaito();
        parent::tearDown();
    }
}
