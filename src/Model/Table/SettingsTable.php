<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppSettingTable;
use App\Model\Table\EntriesTable;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use \Stopwatch\Lib\Stopwatch;

class SettingsTable extends AppSettingTable
{
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
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmptyString('name')
            ->add(
                'name',
                [
                    'maxLength' => [
                        'rule' => ['maxLength', 255],
                    ],
                ]
            );

        $validator
            ->allowEmptyString('value')
            ->add(
                'value',
                [
                    'maxLength' => [
                        'rule' => ['maxLength', 255],
                    ],
                    'subjectMaxLength' => [
                        'rule' => [$this, 'validateSubjectMaxLength'],
                        'message' => __('vld.settings.subjectMaxLength', EntriesTable::SUBJECT_MAXLENGTH)
                    ],
                ]
            );

        return $validator;
    }

    /* @td getSettings vs Load why to functions? */

    /**
     * Reads settings from DB and returns them in a compact array
     *
     * Note that this is the stored config in the DB. It may differ from the
     * current config used by the app in Config::read('Saito.Settings'), e.g.
     * when modified with a load-preset.
     *
     * @throws \RuntimeException
     * @return array Settings
     */
    public function getSettings()
    {
        $settings = $this->find()->all();
        if (empty($settings)) {
            throw new \RuntimeException(
                'No settings found in settings table.'
            );
        }
        $compact = [];
        foreach ($settings as $result) {
            $compact[$result->get('name')] = $result->get('value');
        }

        $this->_fillOptionalEmailAddresses($compact);

        return $compact;
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

        $cacheKey = 'Saito.appSettings.' . Configure::read('Saito.v');

        $settings = Cache::remember(
            $cacheKey,
            function () {
                return $this->getSettings();
            }
        );
        if ($preset) {
            $settings = $preset + $settings;
        }
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
     * {@inheritDoc}
     */
    public function validateSubjectMaxLength($value, array $context)
    {
        if ($context['data']['name'] === 'subject_maxlength') {
            return (int)$context['data']['value'] <= EntriesTable::SUBJECT_MAXLENGTH;
        }

        return true;
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
