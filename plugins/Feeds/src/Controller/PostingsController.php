<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Feeds\Controller;

use App\Controller\AppController;
use App\Model\Table\EntriesTable;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Feeds\Model\Behavior\FeedsPostingBehavior;

/**
 * Feed Posting Controller
 *
 * @property EntriesTable $Entries
 */
class PostingsController extends AppController
{
    public $helpers = ['Feeds.Feeds'];

    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();

        /** @var EntriesTable */
        $EntriesTable = $this->loadModel('Entries');
        $EntriesTable->addBehavior(FeedsPostingBehavior::class);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['new', 'threads']);
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
            ->where(['category_id IN' => $this->CurrentUser->getCategories()->getAll('read')]);
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
            ->where([
                'category_id IN' => $this->CurrentUser->getCategories()->getAll('read'),
                'pid' => 0,
            ]);
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
        if ($this->RequestHandler->prefers('rss')) {
            return;
        }
        throw new BadRequestException();
    }
}
