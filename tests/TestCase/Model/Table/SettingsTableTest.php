<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2014-2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Test\TestCase\Model\Table;

use App\Test\Fixture\SettingFixture;
use Cake\Core\Configure;
use Saito\Test\Model\Table\SaitoTableTestCase;

class SettingsTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Settings';

    public $fixtures = ['app.setting'];

    public function settingsDataProvider()
    {
        $data = (new SettingFixture())->records;
        $extracted = array_combine(array_column($data, 'name'), array_column($data, 'value'));
        $extracted['edit_delay'] *= 60;

        return [[$extracted]];
    }

    public function testFillOptionalMailAddresses()
    {
        $Settings = $this->getMockForModel('Settings', ['_compactKeyValue']);

        $returnValue = [
            'edit_delay' => 0,
            'forum_email' => 'foo@bar.com',
        ];

        $Settings->expects($this->once())
            ->method('_compactKeyValue')
            ->will($this->returnValue($returnValue));
        $result = $Settings->getSettings();

        $expected = 'foo@bar.com';
        $this->assertEquals($expected, $result['forum_email']);
        $this->assertEquals($expected, $result['email_contact']);
        $this->assertEquals($expected, $result['email_register']);
        $this->assertEquals($expected, $result['email_system']);
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

    public function tearDown()
    {
        $this->Table->clearCache();
        parent::tearDown();
    }
}
