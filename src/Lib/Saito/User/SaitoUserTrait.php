<?php

namespace Saito\User;

use App\Model\Entity\User;
use Cake\Core\Exception\Exception;
use Cake\I18n\Time;
use Saito\App\Registry;

/**
 * Main implementor of ForumsUserInterface.
 *
 * @package Saito\User
 */
trait SaitoUserTrait
{
    /**
     * User ID
     *
     * @var int
     */
    protected $_id = null;

    /**
     * Stores if a user is logged in
     *
     * @var bool
     */
    protected $_isLoggedIn = false;

    /**
     * User settings
     *
     * @var array
     */
    protected $_settings = null;

    /**
     * {@inheritDoc}
     */
    public function setSettings($settings)
    {
        if (empty($settings)) {
            $this->_id = null;
            $this->_settings = null;
            $this->_isLoggedIn = false;

            return;
        } elseif ($settings instanceof User) {
            $settings = $settings->toArray();
        }

        if (empty($settings) || !is_array($settings)) {
            throw new \RuntimeException("Can't set user.", 1434705388);
        }

        if (!empty($settings['id'])) {
            $this->_id = (int)$settings['id'];
            $this->_isLoggedIn = true;
        }

        $this->_settings = $settings;

        // perf-cheat
        if (!empty($this->_settings['last_refresh'])) {
            $this->_settings['last_refresh_unix'] = dateToUnix($this->_settings['last_refresh']);
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
     * Set single setting.
     *
     * @param string $setting setting-key
     * @param mixed $value value to set
     * @return void
     */
    public function set($setting, $value)
    {
        $this->_settings[$setting] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * {@inheritDoc}
     */
    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    /**
     * {@inheritDoc}
     */
    public function isUser($user)
    {
        if (is_numeric($user)) {
            $id = (int)$user;
        } elseif ($user instanceof ForumsUserInterface || $user instanceof User) {
            $id = $user->get('id');
        } else {
            throw new \InvalidArgumentException("Can't compare users.", 1434704215);
        }

        return $id === $this->getId();
    }

    /**
     * Checks if user is forbidden.
     *
     * @return bool|string
     */
    public function isForbidden()
    {
        if ($this->get('user_lock')) {
            return 'locked';
        }
        if ($this->get('activate_code')) {
            return 'unactivated';
        }

        return false;
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole()
    {
        if ($this->_id === null) {
            return 'anon';
        } else {
            return $this->get('user_type');
        }
    }

    /**
     * Check if user has permission to access a resource.
     *
     * @param string $resource resource
     * @return bool
     */
    public function permission($resource)
    {
        $permission = Registry::get('Permission');

        return $permission->check($this->getRole(), $resource);
    }
}
