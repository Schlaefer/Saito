<?php

namespace Saito\Test\Model\Table;

class SmiliesTest extends SaitoTableTestCase
{
    public $tableClass = 'Smilies';

    public $fixtures = ['app.smiley'];

    public function testCacheClearAfterDelete()
    {
        $this->Table = $this->getMockforTable('Smilies', ['clearCache']);
        $this->Table->expects($this->once())
            ->method('clearCache');
        $Entity = $this->Table->get(1);
        $this->Table->delete($Entity);
    }

    public function testCacheClearAfterSave()
    {
        $this->Table = $this->getMockforTable('Smilies', ['clearCache']);
        $this->Table->expects($this->once())
            ->method('clearCache');
        $Entity = $this->Table->get(1);
        $Entity->set('code', '?:');
        $this->Table->save($Entity);
    }
}
