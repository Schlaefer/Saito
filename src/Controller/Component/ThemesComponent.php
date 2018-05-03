<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Saito\RememberTrait;
use Saito\User\ForumsUserInterface;

class ThemesComponent extends Component
{
    use InstanceConfigTrait;

    /**
     * Default configuration for InstanceConfigTrait
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Sets theme
     *
     * @param string $theme theme to set
     * @return void
     */
    public function set($theme = null): void
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
    public function setDefault(): void
    {
        $this->_setTheme($this->getDefaultTheme());
    }

    /**
     * Activates theme.
     *
     * @param string $theme theme name
     * @return void
     */
    protected function _setTheme($theme): void
    {
        $this->_registry->getController()->viewBuilder()->setTheme($theme);
    }

    /**
     * Get theme for specific user.
     *
     * @param ForumsUserInterface $user current user
     * @return string current theme for user
     */
    public function getThemeForUser(ForumsUserInterface $user): string
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
    public function getAvailable(ForumsUserInterface $user): array
    {
        $global = $this->getConfig('available', []);

        $users = $this->getConfig('users', []);
        $userId = $user->getId();
        $users = isset($users[$userId]) ? $users[$userId] : [];
        $available = array_merge($global, $users);

        $available[] = $this->getDefaultTheme();
        $available = array_unique($available);

        return $available;
    }

    /**
     * Get default theme.
     *
     * @return string default theme
     */
    protected function getDefaultTheme(): string
    {
        return $this->getConfig('default');
    }

    /**
     * Resolves theme
     *
     * @param string $theme theme to resolve
     * @param array $available available themes
     * @return string
     */
    protected function _resolveTheme($theme, array $available): string
    {
        return in_array($theme, $available) ? $theme : $this->getDefaultTheme();
    }
}
