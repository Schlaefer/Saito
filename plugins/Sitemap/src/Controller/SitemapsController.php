<?php

namespace Sitemap\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Sitemap\Lib\SitemapCollection;

class SitemapsController extends AppController
{
    public $uses = false;

    public $helpers = [
            'Sitemap.Sitemap'
    ];

    public $generators = [
            'SitemapEntries'
    ];

    protected $_Generators = null;

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['index', 'file']);
        $this->response->disableCache();
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
            throw new BadRequestException;
        }
        try {
            $this->set('urls', $this->_Generators->content($file));
        } catch (\Exception $e) {
            throw new BadRequestException;
        }
    }
}
