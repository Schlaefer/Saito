<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Entry;
use App\Model\Table\DraftsTable;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Test\Model\Table\SaitoTableTestCase;

/**
 * App\Model\Table\DraftsTable Test Case
 */
class DraftsTableTest extends SaitoTableTestCase
{
    /** @var DraftsTable */
    public $Drafts;

    public $fixtures = [
        'app.Draft'
    ];

    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()
            ->exists('Drafts') ? [] : ['className' => DraftsTable::class];
        $this->Drafts = TableRegistry::getTableLocator()->get('Drafts', $config);
    }

    public function tearDown()
    {
        unset($this->Drafts);

        parent::tearDown();
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            TimestampBehavior::class,
            $this->Drafts->getBehavior('Timestamp')
        );
    }

    public function testDeleteDraftForPosting()
    {
        $fixtures = [
            [
                'd' => ['user_id' => 1, 'pid IS NULL'],
                'e' => ['user_id' => 1, 'pid' => 0],
            ],
            [
                'd' => ['user_id' => 3, 'pid' => 4],
                'e' => ['user_id' => 3, 'pid' => 4],
            ],
        ];

        foreach ($fixtures as $where) {
            $count = $this->Drafts->find()->where($where['d'])->count();
            $this->assertEquals(1, $count);

            $posting = new Entry($where['e']);
            $this->Drafts->deleteDraftForPosting($posting);

            $count = $this->Drafts->find()->where($where['d'])->count();
            $this->assertEquals(0, $count);
        }
    }

    public function testTrimSubjectAndText()
    {
        $this->Drafts->setConfig('subject_maxlength', 100);
        $fields = ['subject' => ' foo ', 'text' => ' bar '];
        $draft = $this->Drafts->newEntity($fields);

        $this->assertEquals('foo', $draft->get('subject'));
        $this->assertEquals('bar', $draft->get('text'));
    }

    public function testValidateOneNotEmptySuccess()
    {
        $fields = ['subject' => '', 'text' => 'foo'];
        $draft = $this->Drafts->newEntity($fields);

        $success = $this->Drafts->save($draft);

        $this->assertNotEmpty($success);
        $this->assertEmpty($draft->getError('oneNotEmpty'));
    }

    public function testValidateOneNotEmptyFailure()
    {
        $fields = ['subject' => '', 'text' => ''];
        $draft = $this->Drafts->newEntity($fields);

        $success = $this->Drafts->save($draft);

        $this->assertFalse($success);
        $this->assertNotEmpty($draft->getError('oneNotEmpty'));
    }

    public function testValidationSubjectMaxLengthFailure()
    {
        $max = 5;
        $this->Drafts->setConfig('subject_maxlength', $max);
        $draft = $this->Drafts->newEntity(['subject' => str_pad('', $max + 1, '0')]);
        $this->assertArrayHasKey('maxLength', $draft->getError('subject'));
    }

    public function testOutdatedGc()
    {
        $count = $this->Drafts->find()->all()->count();
        $this->assertEquals(2, $count);

        $cron = Registry::get('Cron');
        $cron->execute();

        $count = $this->Drafts->find()->all()->count();
        $this->assertEquals(1, $count);
    }
}
