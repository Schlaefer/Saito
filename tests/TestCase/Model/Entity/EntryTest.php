<?php

namespace App\Test\TestCase\Entity;

use App\Model\Entity\Entry;
use Cake\ORM\TableRegistry;
use Saito\Test\SaitoTestCase;

class EntryTest extends SaitoTestCase
{

    public $fixtures = ['app.category', 'app.entry', 'app.user'];

    public function testIsRoot()
    {
        $postings = TableRegistry::get('Entries');

        $posting = $postings->get(8);
        $this->assertFalse($posting->isRoot());

        $posting = $postings->get(4);
        $this->assertTrue($posting->isRoot());
    }
}
