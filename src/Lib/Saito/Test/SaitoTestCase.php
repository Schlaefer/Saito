<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use Cake\TestSuite\TestCase;

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
