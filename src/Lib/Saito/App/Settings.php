<?php

namespace Saito\App;

class Settings implements \ArrayAccess
{

    protected $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function offsetExists($offset)
    {
        return isset($this->settings[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null|mixed null if property is not set
     */
    public function offsetGet($offset)
    {
        if (!isset($this->settings[$offset])) {
            return null;
        }
        return $this->settings[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->settings[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->settings[$offset]);
    }
}
