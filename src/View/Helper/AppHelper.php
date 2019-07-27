<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * App Helper
 *
 * @property FormHelper $Form
 * @property HtmlHelper $Html
 * @property UrlHelper $Url
 */
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
