<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test\User\ReadPostings;

use App\Model\Table\UserReadsTable;
use Cake\Core\Configure;
use Saito\Test\Model\Table\SaitoTableTestCase;
use Saito\User\CurrentUser\CurrentUser;
use Saito\User\LastRefresh\LastRefreshDummy;
use Saito\User\ReadPostings\ReadPostingsDatabase;

class ReadPostingsDatabaseTest extends SaitoTableTestCase
{
    public $tableClass = 'UserReads';

    /** @var UserReadsTable */
    public $Table;

    public $fixtures = [
        'app.Entry',
        'app.UserRead',
        'app.User',
    ];

    public function setUp()
    {
        parent::setUp();
        Configure::write('Saito.Settings.topics_per_page', 10);
    }

    public function testGarbageCollectionOnEmptyTable()
    {
        $rp = $this->getMock();

        $this->Table->deleteAll([]);
        $this->assertEquals(0, $this->Table->find()->all()->count());

        $rp->garbageCollection();
    }

    public function testGarbageCollectionOnRemovedPosting()
    {
        $rp = $this->getMock();

        $this->Table->Entries->getTarget()->deleteAll([]);
        $this->assertEquals(0, $this->Table->Entries->find()->all()->count());

        $rp->garbageCollection();

        // No change
        $this->assertEquals(2, $this->Table->findByUserId(1)->count());
    }

    public function testGarbageCollectionSuccess()
    {
        $rp = $this->getMock(['postingsPerUser']);
        $rp->expects($this->once())
            ->method('postingsPerUser')
            ->willReturn(1);

        $this->assertEquals(2, $this->Table->findByUserId(1)->count());

        $rp->garbageCollection();

        $this->assertEquals(1, $this->Table->findByUserId(1)->count());
        $this->assertFalse($this->Table->exists(['id' => 2]));
        $this->assertTrue($this->Table->exists(['id' => 3]));
    }

    public function testDelete()
    {
        $rp = $this->getMock();

        $this->assertGreaterThan(0, $this->Table->findByUserId(1)->count());

        $rp->delete();

        $this->assertEquals(0, $this->Table->findByUserId(1)->count());
    }

    private function getMock(array $methods = [])
    {
        $CU = new CurrentUser(['id' => 1]);
        $CU->setLastRefresh(new LastRefreshDummy($CU));

        $args = [$CU, $this->Table, $this->Table->Entries->getTarget()];

        if (!$methods) {
            return new ReadPostingsDatabase(...$args);
        }

        $RP = $this->getMockBuilder(ReadPostingsDatabase::class)
            ->setMethods($methods)
            ->setConstructorArgs($args)
            ->getMock();

        return $RP;
    }
}
