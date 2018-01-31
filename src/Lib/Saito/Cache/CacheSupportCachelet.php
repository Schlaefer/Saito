<?php

namespace Saito\Cache;

abstract class CacheSupportCachelet implements CacheSupportCacheletInterface
{

    /**
     * get cachelet id
     *
     * @return mixed
     */
    public function getId()
    {
        if (!empty($this->_title)) {
            return $this->_title;
        }

        return preg_replace(
            '/Saito\\\Cache\\\(.*)CacheSupportCachelet/',
            '\\1',
            get_class($this)
        );
    }
}
