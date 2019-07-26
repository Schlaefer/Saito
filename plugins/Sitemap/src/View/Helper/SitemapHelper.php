<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Sitemap\View\Helper;

use Cake\View\Helper;
use Cake\View\Helper\UrlHelper;

/**
 * @property UrlHelper $Url
 */
class SitemapHelper extends Helper
{
    public $helpers = ['Url'];

    /**
     * Get sitemap-URL
     *
     * @return string
     */
    public function sitemapUrl()
    {
        return $this->baseUrl() . 'sitemap.xml';
    }

    /**
     * Get base-URL
     *
     * @return string
     */
    public function baseUrl()
    {
        return $this->Url->build('/', true);
    }
}
