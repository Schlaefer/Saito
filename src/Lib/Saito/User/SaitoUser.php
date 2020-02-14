<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User;

use Cake\Utility\Hash;

/**
 * Represents a registered user with all knowledge stored offline
 */
class SaitoUser implements ForumsUserInterface
{
    use ForumsUserTrait;

    /**
     * User settings
     *
     * @var array
     */
    protected $_settings = null;

    /**
     * Constructor.
     *
     * @param array $settings user-settings
     */
    public function __construct(?array $settings = null)
    {
        if ($settings !== null) {
            $this->setSettings($settings);
        }
    }

    /**
     * Sets all (and replaces existing) settings for a user
     *
     * @param array $settings Settings
     * @return void
     */
    public function setSettings(array $settings): void
    {
        $this->_settings = $settings;

        /// performance cheat
        if (!empty($this->_settings['last_refresh'])) {
            $this->_settings['last_refresh_unix'] = dateToUnix($this->_settings['last_refresh']);
        }

        /// performance cheat
        // adds a property 'ignores' which in a array holds all users ignored by this users as keys:
        // ['<key is user-id of ignored user> => <trueish>, …]
        if (!empty($this->_settings['user_ignores'])) {
            $this->_settings['ignores'] = array_fill_keys(
                Hash::extract(
                    $this->_settings,
                    'user_ignores.{n}.blocked_user_id'
                ),
                1
            );
            unset($this->_settings['user_ignores']);
        }
    }

    /**
     * Get single user setting.
     *
     * @param string $setting setting-key
     * @return null|mixed null if setting not found
     */
    public function get($setting)
    {
        if (!isset($this->_settings[$setting])) {
            return null;
        }

        return $this->_settings[$setting];
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $setting, $value)
    {
        $this->_settings[$setting] = $value;
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->_settings;
    }
}
