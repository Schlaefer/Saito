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
use Cake\View\Helper\IdGeneratorTrait;

/**
 * App Helper
 *
 * @property FormHelper $Form
 * @property HtmlHelper $Html
 * @property UrlHelper $Url
 */
class AppHelper extends Helper
{
    use IdGeneratorTrait;

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

    /**
     * Generate an ID suitable for use in an ID attribute.
     *
     * @param string $value The value to convert into an ID.
     * @return string The generated id.
     */
    public function domId(string $value)
    {
        return $this->_domId($value);
    }
}
