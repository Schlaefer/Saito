<?php

namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Saito\Test\Model\Table\SaitoTableTestCase;

class SettingsTableTest extends SaitoTableTestCase
{

    public $tableClass = 'Settings';

    public $fixtures = ['app.setting'];

    protected $_settingsCompact = [
        'autolink' => '1',
        'block_user_ui' => '1',
        'edit_delay' => 180,
        'edit_period' => '20',
        'email_contact' => 'contact@example.com',
        'email_register' => 'register@example.com',
        'email_system' => 'system@example.com',
        'forum_email' => 'forum_email@example.com',
        'forum_name' => 'macnemo',
        'map_enabled' => '0',
        'quote_symbol' => '>',
        'shoutbox_enabled' => '1',
        'shoutbox_max_shouts' => '5',
        'smilies' => '1',
        'subject_maxlength' => '40',
        'thread_depth_indent' => '25',
        'timezone' => 'UTC',
        'topics_per_page' => '20',
        'tos_enabled' => '1',
        'tos_url' => 'http://example.com/tos-url.html/',
        'category_chooser_global' => '0',
        'category_chooser_user_override' => '1',
    ];

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

    public function testAfterSave()
    {
        $setting = $this->Table->get('forum_name');
        $setting->set('value', 'fuselage');
        $this->Table->save($setting);

        $result = $this->Table->getSettings();
        $expected = array_merge(
            $this->_settingsCompact,
            ['forum_name' => 'fuselage']
        );
        $this->assertEquals($result, $expected);
    }

    public function testGetSettings()
    {
        $result = $this->Table->getSettings();
        $expected = $this->_settingsCompact;
        $this->assertEquals($result, $expected);
    }

    /**
     *
     *
     * preset must force a refresh
     */
    public function testLoadWithPreset()
    {
        $this->Table->load();

        $preset = ['lock' => 'hatch', 'timezone' => 'island'];
        $this->Table->load($preset);
        $result = Configure::read('Saito.Settings');
        $expected = $this->_settingsCompact;
        $expected['lock'] = 'hatch';
        $expected['timezone'] = 'island';
        $this->assertEquals($result, $expected);
    }

    public function testLoad()
    {
        Configure::write('Saito.Settings', null);
        $this->Table->load();
        $result = Configure::read('Saito.Settings');
        $expected = $this->_settingsCompact;
        $this->assertEquals($result, $expected);
    }

    public function tearDown()
    {
        $this->Table->clearCache();
        parent::tearDown();
    }
}
