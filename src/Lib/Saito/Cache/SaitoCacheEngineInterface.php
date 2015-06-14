<?php

namespace Saito\Cache;

interface SaitoCacheEngineInterface
{

    /**
     * read
     *
     * @param string $name name
     * @return mixed
     */
    public function read($name);

    /**
     * Write
     *
     * @param string $name name
     * @param mixed $content content
     * @return void
     */
    public function write($name, $content);
}
