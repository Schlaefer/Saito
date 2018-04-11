<?php

namespace Sitemap\View\Helper;

use Cake\View\Helper;

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
