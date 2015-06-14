<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Saito\RememberTrait;
use Saito\SettingsTrait;
use Saito\User\ForumsUserInterface;

class ThemesComponent extends Component
{
    use SettingsTrait;

    protected $_Controller = null;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->_Controller = $this->_registry->getController();
    }

    /**
     * Sets and/or gets current theme
     *
     * @param array $settings name string or config array
     * @param ForumsUserInterface $CU current user
     * @return void
     * @throws \InvalidArgumentException
     */
    public function theme(array $settings, ForumsUserInterface $CU)
    {
        $this->settings($settings);
        $theme = $this->getThemeForUser($CU);
        if (empty($theme)) {
            throw new \InvalidArgumentException('Can\'t set Theme.');
        }
        $this->_setTheme($theme);
    }

    /**
     * Set used theme to default theme.
     *
     * @return void
     */
    public function setDefault()
    {
        $this->_setTheme($this->_getDefault());
    }

    /**
     * Activates theme.
     *
     * @param string $theme theme name
     * @return void
     */
    protected function _setTheme($theme)
    {
        $this->_Controller->theme = $theme;
    }

    /**
     * Get themes for specific user.
     *
     * @param ForumsUserInterface $user current user
     * @return array
     */
    public function getThemeForUser(ForumsUserInterface $user)
    {
        $theme = $user->get('user_theme');
        $available = $this->getAvailable($user);
        return in_array($theme, $available) ? $theme : $this->_getDefault();
    }

    /**
     * Gets all available themes for user
     *
     * @param ForumsUserInterface $user current user
     * @return array
     */
    public function getAvailable(ForumsUserInterface $user)
    {
        $global = $this->settings('available') ?: [];

        $users = $this->settings('users') ?: [];
        $userId = $user->getId();
        $users = isset($users[$userId]) ? $users[$userId] : [];
        $available = array_merge($global, $users);

        $available[] = $this->_getDefault();
        $available = array_unique($available);

        return $available;
    }

    /**
     * Get default theme.
     *
     * @return string
     */
    protected function _getDefault()
    {
        return $this->settings('default');
    }
}
