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
use Saito\App\Registry;

/**
 * Represents a registered user with all knowledge stored offline
 */
class SaitoUser implements ForumsUserInterface
{
    /**
     * User ID
     *
     * @var int
     */
    protected $_id = null;

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
     * {@inheritDoc}
     */
    public function setSettings(array $settings): void
    {
        if (!empty($settings['id'])) {
            $this->_id = (int)$settings['id'];
        }

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
     * {@inheritDoc}
     */
    public function getSettings(): array
    {
        return $this->_settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * {@inheritDoc}
     */
    public function isUser(ForumsUserInterface $user): bool
    {
        return $user->getId() === $this->getId();
    }

    /**
     * Checks if user is forbidden.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool)$this->get('user_lock');
    }

    /**
     * Checks if user is forbidden.
     *
     * @return bool
     */
    public function isActivated(): bool
    {
        return !$this->get('activate_code');
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->get('user_type');
    }

    /**
     * Check if user has permission to access a resource.
     *
     * @param string $resource resource
     * @return bool
     */
    public function permission(string $resource): bool
    {
        $permission = Registry::get('Permission');

        return $permission->check($this->getRole(), $resource);
    }
}
