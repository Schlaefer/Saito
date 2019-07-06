<?php

namespace Saito\User;

use App\Model\Entity\User;

interface ForumsUserInterface
{

    /**
     * Get a user setting.
     *
     * @param string $setting Setting to get.
     * @return mixed
     */
    public function get($setting);

    /**
     * Set a user setting.
     *
     * @param string $setting key
     * @param mixed $value value
     * @return void
     */
    public function set($setting, $value);

    /**
     * Set all settings
     *
     * @param array|User $settings Settings
     * @return void
     */
    public function setSettings($settings);

    /**
     * Get all settings
     *
     * @return array
     */
    public function getSettings();

    /**
     * Get user's id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get user's role.
     *
     * @return string
     */
    public function getRole();

    /**
     * Checks if the user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn();

    /**
     * Checks if the user is forbidden.
     *
     * @return bool
     */
    public function isLocked() : bool;

    /**
     * Checks if the user is activated
     *
     * @return bool
     */
    public function isActivated() : bool;

    /**
     * Checks if the user is the same user as $user
     *
     * @param int|ForumsUserInterface $user - User-ID or user to check against.
     * @return bool
     */
    public function isUser($user);
}
