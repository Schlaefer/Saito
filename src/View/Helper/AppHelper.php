<?php

namespace App\View\Helper;

use Cake\View\Helper;

class AppHelper extends Helper
{

    protected static $_tagId = 0;

    /**
     * tag id
     *
     * @return string
     */
    public static function tagId()
    {
        return 'id' . static::$_tagId++;
    }
}
