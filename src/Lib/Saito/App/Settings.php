<?php

namespace Saito\App;

class Settings implements \ArrayAccess
{

    protected $settings;

    /**
     * {@inheritDoc}
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->settings[$offset]);
    }

    /**
     * {@inheritDoc}
     *
     * @return null|mixed null if property is not set
     */
    public function offsetGet($offset)
    {
        if (!isset($this->settings[$offset])) {
            return null;
        }
        return $this->settings[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->settings[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->settings[$offset]);
    }
}
