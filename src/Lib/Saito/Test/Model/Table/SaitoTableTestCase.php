<?php

namespace Saito\Test\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\SaitoTestCase;
use Saito\User\SaitoUser;

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
    public function setUp()
    {
        parent::setUp();

        TableRegistry::clear();
        $this->Table = TableRegistry::get($this->tableClass);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        unset($this->Table);
        parent::tearDown();
    }
}
