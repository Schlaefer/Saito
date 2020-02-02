<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Sitemap\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Sitemap\Lib\SitemapCollection;

class SitemapsController extends AppController
{
    public $uses = false;

    public $helpers = [
            'Sitemap.Sitemap',
    ];

    public $generators = [
            'SitemapEntries',
    ];

    protected $_Generators = null;

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index', 'file']);
        $this->response = $this->response->withDisabledCache();
        $this->_Generators = new SitemapCollection($this->generators, $this);
    }

    /**
     * Index
     *
     * @return void
     */
    public function index()
    {
        $this->set('files', $this->_Generators->files());
    }

    /**
     *
     * @param string $file filename
     * @return void
     * @throws BadRequestException
     */
    public function file($file)
    {
        if (empty($file)) {
            throw new BadRequestException();
        }
        try {
            $this->set('urls', $this->_Generators->content($file));
        } catch (\Exception $e) {
            throw new BadRequestException();
        }
    }
}
