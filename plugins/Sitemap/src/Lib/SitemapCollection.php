<?php

namespace Sitemap\Lib;

use Cake\Cache\Cache;
use Cake\Controller\Controller;

class SitemapCollection
{
    protected $_Generators = [];

    /**
     * Constructor
     *
     * @param array $generators generators
     * @param Controller $Controller Controller
     */
    public function __construct(array $generators, Controller $Controller)
    {
        if (!Cache::config('sitemap')) {
            Cache::config(
                'sitemap',
                [
                    'prefix' => 'saito_',
                    'engine' => 'File',
                    'groups' => ['sitemap'],
                    'path' => CACHE
                ]
            );
        }
        foreach ($generators as $name) {
            $this->_add($name, $Controller);
        }
    }

    /**
     * Add generator
     *
     * @param string $name generator
     * @param Controller $Controller controller
     * @return void
     */
    protected function _add($name, Controller $Controller)
    {
        $name = 'Sitemap\\Lib\\' . $name;
        $this->_Generators[$name] = new $name($Controller);
    }

    /**
     * Get list of sitemap files
     *
     * @return array
     */
    public function files()
    {
        $files = [];
        foreach ($this->_Generators as $Generator) {
            $files += $Generator->files();
        }

        return $files;
    }

    /**
     * Generates content for file
     *
     * @param string $file filename
     * @return string
     */
    public function content($file)
    {
        $contents = [];
        foreach ($this->_Generators as $Generator) {
            $content = $Generator->content($file);
            if ($content) {
                $contents = array_merge($contents, $content);
            }
        }

        return $contents;
    }
}
