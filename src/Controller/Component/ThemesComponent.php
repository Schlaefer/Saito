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

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->settings($config);
    }

    /**
     * Sets theme
     *
     * @param string $theme theme to set
     * @return void
     */
    public function set($theme = null)
    {
        $controller = $this->_registry->getController();
        $user = $controller->CurrentUser;

        if ($theme === null) {
            $theme = $this->getThemeForUser($user);
        } else {
            $theme = $this->_resolveTheme($theme, $this->getAvailable($user));
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
        $this->_registry->getController()->viewBuilder()->theme($theme);
    }

    /**
     * Get theme for specific user.
     *
     * @param ForumsUserInterface $user current user
     * @return array
     */
    public function getThemeForUser(ForumsUserInterface $user)
    {
        $theme = $user->get('user_theme');
        $available = $this->getAvailable($user);

        return $this->_resolveTheme($theme, $available);
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

    /**
     * Resolves theme
     *
     * @param string $theme theme to resolve
     * @param array $available available themes
     * @return string
     */
    protected function _resolveTheme($theme, array $available)
    {
        return in_array($theme, $available) ? $theme : $this->_getDefault();
    }
}
