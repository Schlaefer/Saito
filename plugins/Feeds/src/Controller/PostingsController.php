<?php

namespace Feeds\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Feeds\Model\Behavior\FeedsPostingBehavior;

class PostingsController extends AppController
{
    public $helpers = ['Feeds.Feeds'];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadModel('Entries')->addBehavior(FeedsPostingBehavior::class);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event): void
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['new', 'threads']);
        $this->viewBuilder()->enableAutoLayout(false);
        $this->viewBuilder()->setTemplate('posting');
    }

    /**
     * RSS-feed for postings.
     *
     * @return void
     */
    public function new(): void
    {
        $this->checkRss();

        $entries = $this->Entries
            ->find('feed')
            ->order(['last_answer' => 'DESC']);
        $this->set('entries', $entries);

        $this->set('titleForPage', __d('feeds', 'postings.new.t'));
    }

    /**
     * RSS-feed for new threads.
     *
     * @return void
     */
    public function threads(): void
    {
        $this->checkRss();

        $entries = $this->Entries
            ->find('feed')
            ->where(['pid' => 0])
            ->order(['last_answer' => 'DESC']);
        $this->set('entries', $entries);

        $this->set('titleForPage', __d('feeds', 'threads.new.t'));
    }

    /**
     * Check that request is Rss
     *
     * Can't use beforeFilter because RequestHandlerComponent::startup() not called
     * and thus RequestHandler uninitialized
     *
     * @throws BadRequestException
     * @return void
     */
    private function checkRss(): void
    {
        if ($this->RequestHandler->isRss()) {
            return;
        }
        throw new BadRequestException();
    }
}
