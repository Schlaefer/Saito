<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
        if (!Cache::getConfig('sitemap')) {
            Cache::setConfig(
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
     * @return array
     */
    public function content(string $file): array
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
