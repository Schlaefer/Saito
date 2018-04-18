<?php

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Saito\App\Registry;
use \Stopwatch\Lib\Stopwatch;

class SettingsTable extends AppSettingTable
{

    public $validate = [
        'name' => [
            'rule' => ['between', 1, 255],
            'allowEmpty' => false
        ],
        'value' => [
            'rule' => ['between', 0, 255],
            'allowEmpty' => true
        ]
    ];

    protected $_optionalEmailFields = [
        'email_contact',
        'email_register',
        'email_system'
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->setPrimaryKey('name');
        $this->setTable('settings');
    }

    /* @td getSettings vs Load why to functions? */

    /**
     * Reads settings from DB and returns them in a compact array
     *
     * Note that this is the stored config in the DB. It may differ from the
     * current config used by the app in Config::read('Saito.Settings'), e.g.
     * when modified with a load-preset.
     *
     * @throws UnexpectedValueException
     * @return array Settings
     */
    public function getSettings()
    {
        $settings = $this->find();
        if (empty($settings)) {
            throw new UnexpectedValueException(
                'No settings found in settings table.'
            );
        }
        $settings = $this->_compactKeyValue($settings);

        // edit_delay is normed to seconds
        $this->_normToSeconds($settings, 'edit_delay');
        $this->_fillOptionalEmailAddresses($settings);

        return $settings;
    }

    /**
     * Loads settings from storage into Configuration `Saito.Settings`
     *
     * @param array $preset allows to overwrite loaded values
     * @return array Settings
     */
    public function load($preset = [])
    {
        Stopwatch::start('Settings->getSettings()');

        $settings = Cache::remember(
            'Saito.appSettings',
            function () {
                return $this->getSettings();
            }
        );
        if ($preset) {
            $settings = $preset + $settings;
        }
        // @td 5.1 move facility from DB to config file
        Configure::write(
            'App.defaultTimezone',
            Configure::read('Saito.Settings.timezone')
        );
        Configure::write('Saito.Settings', $settings);
        Stopwatch::end('Settings->getSettings()');

        return $settings;
    }

    /**
     * clear cache
     *
     * @return void
     */
    public function clearCache()
    {
        parent::clearCache();
        Cache::delete('Saito.appSettings');
    }

    /**
     * Returns a key-value array
     *
     * Fast version of Set::combine($results, '{n}.Setting.name',
     * '{n}.Setting.value');
     *
     * @param array $results results
     * @return array
     */
    protected function _compactKeyValue($results)
    {
        $settings = [];
        foreach ($results as $result) {
            $settings[$result->get('name')] = $result->get('value');
        }

        return $settings;
    }

    /**
     * norm to seconds
     *
     * @param array $settings settings
     * @param string $field field
     * @return void
     */
    protected function _normToSeconds(&$settings, $field)
    {
        $settings[$field] = (int)$settings[$field] * 60;
    }

    /**
     * Defaults optional email addresses to main address
     *
     * @param array $settings settings
     * @return void
     */
    protected function _fillOptionalEmailAddresses(&$settings)
    {
        foreach ($this->_optionalEmailFields as $field) {
            if (empty($settings[$field])) {
                $settings[$field] = $settings['forum_email'];
            }
        }
    }
}
