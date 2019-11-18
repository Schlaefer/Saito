<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Entry;
use App\Model\Table\EntriesTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Saito\Test\Model\Table\SaitoTableTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

class EntriesTest extends SaitoTableTestCase
{
    public $tableClass = 'Entries';

    /** @var EntriesTable */
    public $Table;

    public $fixtures = [
        'app.User',
        'app.UserOnline',
        'app.UserRead',
        'app.Entry',
        'app.Category',
        'app.Smiley',
        'app.SmileyCode',
        'app.Setting',
    ];

    public function testCreateSuccessNewThread()
    {
        $category = 1;

        $Drafts = $this->getMockForModel(
            'Drafts',
            ['deleteDraftForPosting']
        );
        $Drafts->expects($this->once())
            ->method('deleteDraftForPosting')
            ->with($this->isInstanceOf(Entry::class));

        $this->Table = $this->getMockForModel(
            'Entries',
            ['dispatchDbEvent'],
            ['className' => 'Saito\Test\Model\Table\EntriesTableMock']
        );
        $this->Table->expects($this->once())
            ->method('dispatchDbEvent')
            ->with('Model.Thread.create', $this->anything());

        //= Setup CurrentUser
        $SaitoUser = CurrentUserFactory::createLoggedIn(
            ['id' => 100, 'username' => 'foo', 'user_type' => 'admin']
        );

        // +1 because str_pad calculates non ascii chars to a string length of 2
        $subject = str_pad(
            'Sübject',
            $this->Table->setting('subject_maxlength') + 1,
            '.'
        );
        $data = [
            'category_id' => $category,
            'name' => 'foo',
            'pid' => 0,
            'subject' => $subject,
            'text' => 'Täxt',
            'user_id' => 100,
        ];

        /*
         * test success
         */
        $expectedThreadId = $this->Table->find()->count() + 1;
        $result = $this->Table->createEntry($data, $SaitoUser)->toArray();
        $expected = $data;
        $expected['tid'] = $expectedThreadId;
        $result = array_intersect_key($result, $expected);
        $this->assertEquals($result, $expected);
    }

    public function testValidationSubjectMaxLengthFailure()
    {
        $max = 5;
        $this->Table->setConfig('subject_maxlength', $max);
        $draft = $this->Table->newEntity(['subject' => str_pad('', $max + 1, '0')]);
        $this->assertArrayHasKey('maxLength', $draft->getError('subject'));
    }

    public function testAnonymizeEntriesFromUser()
    {
        $this->Table->anonymizeEntriesFromUser(3);

        $entriesBeforeActions = $this->Table->find()->count();

        // user has no entries anymore
        $expected = 0;
        $result = $this->Table->find()->where(['user_id' => 3])->count();
        $this->assertEquals($result, $expected);

        // entries are now assigned to user_id 0
        $expected = 7;
        $result = $this->Table->find()->where(['user_id' => 0])->count();
        $this->assertEquals($result, $expected);

        // name is removed
        $expected = 0;
        $result = $this->Table->find()->where(['name' => 'Ulysses'])->count();
        $this->assertEquals($result, $expected);

        $result = $this->Table->get(3);
        $this->assertNull($result->get('name'));

        // edited by is removed
        $expected = 0;
        $result = $this->Table->find()
            ->where(['edited_by' => 'Ulysses'])
            ->count();
        $this->assertEquals($result, $expected);

        $result = $this->Table->get(3);
        $this->assertNull($result->get('edited_by'));

        // ip is removed
        $expected = 0;
        $result = $this->Table->find()->where(['ip' => '1.1.1.1'])->count();
        $this->assertEquals($result, $expected);

        $result = $this->Table->get(3);
        $this->assertNull($result->get('ip'));

        // all entries are still there
        $expected = $entriesBeforeActions;
        $result = $this->Table->find()->count();
        $this->assertEquals($result, $expected);
    }

    public function testGetThreadId()
    {
        $result = $this->Table->getThreadId(1);
        $expected = 1;
        $this->assertEquals($result, $expected);

        $result = $this->Table->getThreadId(5);
        $expected = 4;
        $this->assertEquals($result, $expected);
    }

    public function testGetThreadIdNotFound()
    {
        $this->expectException(RecordNotFoundException::class);
        $this->Table->getThreadId(999);
    }

    public function testTrimSubjectAndText()
    {
        $fields = ['subject' => ' foo ', 'text' => ' bar '];
        $new = $this->Table->newEntity($fields);

        $this->assertEquals('foo', $new->get('subject'));
        $this->assertEquals('bar', $new->get('text'));
    }
}
