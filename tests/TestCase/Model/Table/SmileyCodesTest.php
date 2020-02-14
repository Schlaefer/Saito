<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Saito\Test\Model\Table\SaitoTableTestCase;

class SmileyCodesTest extends SaitoTableTestCase
{
    public $tableClass = 'SmileyCodes';

    public $fixtures = ['app.Smiley', 'app.SmileyCode'];

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

    public function setUp(): void
    {
        parent::setUp();
        $this->Table->clearCache();
    }

    public function tearDown(): void
    {
        $this->Table->clearCache();
        parent::tearDown();
    }
}
