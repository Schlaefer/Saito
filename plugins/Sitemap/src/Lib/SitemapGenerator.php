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

use Cake\Controller\Controller;

abstract class SitemapGenerator
{

    /**
     * Number of URLs per sitemap file.
     *
     * Mostly bound by memory available to PHP on server
     *
     * @var int
     */
    protected $_divider = 20000;

    protected $_Controller;

    protected $_type = null;

    /**
     * @param Controller $Controller controller
     * @throws \Exception
     */
    public function __construct(Controller $Controller)
    {
        $this->_Controller = $Controller;
        if ($this->_type === null) {
            throw new \Exception('SitemapGenerator type not set.', 1559477829);
        }
    }

    /**
     * Returns sitemap file
     *
     * @return array keys: 'url'
     */
    abstract public function files();

    /**
     * @param string $file filename
     * @return mixed
     */
    public function content($file)
    {
        list($type, $params) = $this->_parseFilename($file);
        if ($type !== $this->_type) {
            return false;
        }

        return $this->_content($this->_parseParams($params));
    }

    /**
     *
     * @param string $name filename to parse
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _parseFilename($name)
    {
        preg_match(
            '/sitemap-(?P<type>\w*)(-(?P<params>.*))?(\.(\w*))?$/',
            $name,
            $matches
        );
        if (empty($matches['type'])) {
            throw new \InvalidArgumentException(
                sprintf('File not found for: %s', $name),
                1559477721
            );
        }

        return [$matches['type'], explode('-', $matches['params'])];
    }

    /**
     * Parse and validate params. Should throw exception if params are not valid.
     *
     * @param array $params additional parameters
     * @return mixed processed params
     */
    abstract protected function _parseParams($params);

    /**
     * Generate urls
     *
     * @param array $params additional paramters
     * @return array urls
     */
    abstract protected function _content(array $params): array;

    /**
     * Creates name for sitemap-file
     *
     * @param array $params additional name parameters
     * @return string
     */
    protected function _filename($params = [])
    {
        $filename = "sitemap-{$this->_type}";
        if ($params) {
            $filename .= '-' . implode('-', $params);
        }

        return $filename;
    }
}
