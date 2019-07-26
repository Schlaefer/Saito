<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\App;

class SettingsImmutable
{
    /**
     * The Settings
     *
     * @var array
     */
    protected $settings;

    /**
     * {@inheritDoc}
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get a setting
     *
     * @param string $key setting to get
     * @return mixed setting
     */
    public function get(string $key)
    {
        if (!isset($this->settings[$key])) {
            throw new \RuntimeException("Setting $key not found.", 1524226492);
        }

        return $this->settings[$key];
    }
}
