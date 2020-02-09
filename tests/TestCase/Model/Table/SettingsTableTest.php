<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2014-2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EntriesTable;
use App\Test\Fixture\SettingFixture;
use Cake\Core\Configure;
use Saito\Test\Model\Table\SaitoTableTestCase;

class SettingsTableTest extends SaitoTableTestCase
{
    public $tableClass = 'Settings';

    public $fixtures = ['app.Setting'];

    public function settingsDataProvider()
    {
        $data = (new SettingFixture())->records;
        $extracted = array_combine(array_column($data, 'name'), array_column($data, 'value'));

        return [[$extracted]];
    }

    public function testFillOptionalMailAddresses()
    {
        $address = rand(0, 100) . '@example.com';
        $this->Table->updateAll(['value' => $address], ['name' => 'forum_email']);
        foreach (['email_contact', 'email_register', 'email_system'] as $c) {
            $this->Table->deleteAll(['name' => $c]);
        }

        $result = $this->Table->getSettings();

        $this->assertEquals($address, $result['forum_email']);
        $this->assertEquals($address, $result['email_contact']);
        $this->assertEquals($address, $result['email_register']);
        $this->assertEquals($address, $result['email_system']);
    }

    /**
     * @dataProvider settingsDataProvider
     */
    public function testAfterSave($fixture)
    {
        $setting = $this->Table->get('forum_name');
        $setting->set('value', 'fuselage');
        $this->Table->save($setting);

        $result = $this->Table->getSettings();
        $expected = array_merge(
            $fixture,
            ['forum_name' => 'fuselage']
        );
        $this->assertEquals($result, $expected);
    }

    /**
     * @dataProvider settingsDataProvider
     */
    public function testGetSettings($fixture)
    {
        $result = $this->Table->getSettings();
        $expected = $fixture;
        $this->assertEquals($result, $expected);
    }

    /**
     *
     *
     * preset must force a refresh
     *
     * @dataProvider settingsDataProvider
     */
    public function testLoadWithPreset($fixture)
    {
        $this->Table->load();

        $preset = ['lock' => 'hatch', 'timezone' => 'island'];
        $this->Table->load($preset);
        $result = Configure::read('Saito.Settings');
        $expected = $fixture;
        $expected['lock'] = 'hatch';
        $expected['timezone'] = 'island';
        $this->assertEquals($result, $expected);
    }

    /**
     * @dataProvider settingsDataProvider
     */
    public function testLoad($fixture)
    {
        Configure::write('Saito.Settings', null);
        $this->Table->load();
        $result = Configure::read('Saito.Settings');
        $expected = $fixture;
        $this->assertEquals($result, $expected);
    }

    public function testValidationNameNotEmpty()
    {
        $entity = $this->Table->newEntity(['name' => '', 'value' => 'foo']);
        $this->assertArrayHasKey('_empty', $entity->getError('name'));
    }

    public function testValidationNameMaxLength()
    {
        $entity = $this->Table->newEntity(
            ['name' => str_pad('', 256, 'foo'), 'value' => 'foo']
        );
        $this->assertArrayHasKey('maxLength', $entity->getError('name'));
    }

    public function testValidationValueMaxLength()
    {
        $entity = $this->Table->newEntity(
            ['name' => 'foo', 'value' => str_pad('', 256, 'foo')]
        );
        $this->assertArrayHasKey('maxLength', $entity->getError('value'));
    }

    public function testValidationSubjectMaxLength()
    {
        $max = EntriesTable::SUBJECT_MAXLENGTH;
        $entity = $this->Table->newEntity(
            ['name' => 'subject_maxlength', 'value' => $max + 1]
        );
        $this->assertArrayHasKey('subjectMaxLength', $entity->getError('value'));
        $this->assertStringContainsString((string)$max, $entity->getError('value')['subjectMaxLength']);
    }

    public function tearDown(): void
    {
        $this->Table->clearCache();
        parent::tearDown();
    }
}
