<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Controller\AppController;
use Cake\Controller\Component;
use Cake\Core\InstanceConfigTrait;
use Saito\User\CurrentUser\CurrentUserInterface;

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
    public function initialize(array $config): void
    {
        $this->setConfig($config);
    }

    /**
     * Sets theme
     *
     * @param CurrentUserInterface $user current user
     * @param string $theme theme to set
     * @return void
     */
    public function set(CurrentUserInterface $user, $theme = null): void
    {
        if ($theme === null) {
            $theme = $this->getThemeForUser($user);
        } else {
            $theme = $this->_resolveTheme($theme, $this->getAvailable($user));
        }
        $this->_setTheme($theme);
    }

    /**
     * Applies the global default theme as activate theme.
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
     * @param CurrentUserInterface $user current user
     * @return string current theme for user
     */
    public function getThemeForUser(CurrentUserInterface $user): string
    {
        $theme = $user->get('user_theme');
        $available = $this->getAvailable($user);

        return $this->_resolveTheme($theme, $available);
    }

    /**
     * Gets all available themes for user.
     *
     * @param CurrentUserInterface $user current user
     * @return array
     */
    public function getAvailable(CurrentUserInterface $user): array
    {
        $available = [];

        if ($user->isLoggedIn()) {
            $global = $this->getConfig('available', []);
            $userThemes = $this->getConfig('users', []);
            $userId = $user->getId();
            $userThemes = isset($userThemes[$userId]) ? $userThemes[$userId] : [];
            $available = array_merge($global, $userThemes);
        }

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
