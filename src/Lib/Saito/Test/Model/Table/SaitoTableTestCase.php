<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Saito\Test\SaitoTestCase;

abstract class SaitoTableTestCase extends SaitoTestCase
{

    /**
     * @var string
     */
    public $tableClass;

    /**
     * @var Table
     */
    public $Table;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        TableRegistry::clear();
        $this->Table = TableRegistry::get($this->tableClass);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        unset($this->Table);
        parent::tearDown();
    }
}
