<?php

namespace Saito;

trait SettingsTrait
{

    protected $_settings = [];

    /**
     * Set or get settings.
     *
     * @param string $key key to read or set
     * @param mixed $value value to set
     * @param bool $merge if $key is array merge
     * @return mixed
     */
    public function settings($key, $value = null, $merge = true)
    {
        if ($value === null) {
            if (is_array($key)) {
                if ($merge) {
                    $this->_settings = $key + $this->_settings;
                } else {
                    $this->_settings = $key;
                }
                return $this->_settings;
            } elseif (isset($this->_settings[$key])) {
                return $this->_settings[$key];
            } else {
                return null;
            }
        }
        $this->_settings[$key] = $value;
    }
}
