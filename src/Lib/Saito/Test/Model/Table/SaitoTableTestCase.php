<?php

namespace Saito\Test\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\SaitoTestCase;
use Saito\User\SaitoUser;

abstract class SaitoTableTestCase extends SaitoTestCase {

    /**
     * @var string
     */
    public $tableClass;

    /**
     * @var Table
     */
    public $Table;

    public function setUp()
    {
        parent::setUp();

        TableRegistry::clear();
        $this->Table = TableRegistry::get($this->tableClass);
    }

    public function tearDown()
    {
        unset($this->Table);
        parent::tearDown();
    }

}
