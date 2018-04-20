<?php

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
