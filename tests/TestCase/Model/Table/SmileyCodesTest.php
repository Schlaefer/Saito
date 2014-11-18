<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SmileyCodesTable;
use Cake\ORM\Entity;
use Saito\Cache\CacheSupport;
use Saito\Test\Model\Table\SaitoTableTestCase;

class SmileyCodeTest extends SaitoTableTestCase
{
    public $tableClass = 'SmileyCodes';

    public $fixtures = ['app.smiley', 'app.smiley_code'];

    public function testCacheClearAfterDelete()
    {
        $this->Table = $this->getMockForTable(
            'SmileyCodes',
            ['clearCache']
        );
        $this->Table->expects($this->once())
            ->method('clearCache');
        $Entity = $this->Table->get(1);
        $this->Table->delete($Entity);
    }

    public function testCacheClearAfterSave()
    {
        $this->Table = $this->getMockForTable(
            'SmileyCodes',
            ['clearCache']
        );
        $this->Table->expects($this->once())
            ->method('clearCache');
        $Entity = $this->Table->get(1);
        $Entity->set('code', 'foo');
        $this->Table->save($Entity);
    }

    public function setUp()
    {
        parent::setUp();
        $this->Table->clearCache();
    }

    public function tearDown()
    {
        $this->Table->clearCache();
        parent::tearDown();
    }

}
