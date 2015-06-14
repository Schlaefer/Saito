<?php

namespace Saito;

trait RememberTrait
{

    private $__remember = [];

    private static $__rememberStatic = [];

    /**
     * Remember static
     *
     * @param string $key string
     * @param mixed $value value
     * @return mixed
     */
    protected static function rememberStatic($key, $value = null)
    {
        if ($value === null && is_array($key)) {
            self::$__rememberStatic = $key;
        }
        if (isset(self::$__rememberStatic[$key])) {
            return self::$__rememberStatic[$key];
        }
        if (is_callable($value)) {
            self::$__rememberStatic[$key] = call_user_func($value);
        } else {
            self::$__rememberStatic[$key] = $value;
        }

        return self::$__rememberStatic[$key];
    }

    /**
     * Remember static
     *
     * @param string $key string
     * @param mixed $value value
     * @return mixed
     */
    protected function remember($key, $value)
    {
        if (isset($this->__remember[$key])) {
            return $this->__remember[$key];
        }
        if (is_callable($value)) {
            $this->__remember[$key] = call_user_func($value);
        } else {
            $this->__remember[$key] = $value;
        }

        return $this->__remember[$key];
    }
}
