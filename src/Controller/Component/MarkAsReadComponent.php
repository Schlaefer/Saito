<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Saito\App\Registry;
use Saito\Posting\PostingInterface;

/**
 * Class MarkAsReadComponent
 *
 * @package App\Controller\Component
 */
class MarkAsReadComponent extends Component
{
    /**
     * @var array of Posting
     */
    protected $postings = [];

    /**
     * {@inheritDoc}
     */
    public function shutdown()
    {
        if (empty($this->postings)) {
            return;
        }
        $this->_registry
            ->getController()
            ->CurrentUser
            ->getReadPostings()
            ->set($this->postings);
    }

    /**
     * On next reload
     *
     * @return void
     */
    public function next()
    {
        $CU = Registry::get('CU');
        if (!$CU->isLoggedIn() || !$CU->get('user_automaticaly_mark_as_read')) {
            return;
        }
        $this->_registry->getController()->set('markAsRead', true);
    }

    /**
     * automatic mark-as-read
     *
     * @param array $options options
     * - 'enabled' (bool) - Is enabled. Default: get from current-user preference.
     *
     * @return bool true on refresh
     */
    public function refresh(array $options = [])
    {
        $CU = Registry::get('CU');
        if ($this->request->is('preview') || !$CU->isLoggedIn()) {
            return false;
        }

        $options += [
            'enabled' => $CU->get('user_automaticaly_mark_as_read')
        ];

        if (!$options['enabled']) {
            return false;
        }

        $session = $this->request->getSession();
        $lastRefreshTemp = $session->read('User.last_refresh_tmp');
        if (empty($lastRefreshTemp)) {
            // new session
            $lastRefreshTemp = time();
            $session->write('User.last_refresh_tmp', $lastRefreshTemp);
        }

        if ($this->request->getQuery('mar', false) !== false) {
            // a second session A shall not accidentally mark something as read that isn't read on session B
            if ($lastRefreshTemp > $CU->get('last_refresh_unix')) {
                $CU->getLastRefresh()->set();
            }
            $session->write('User.last_refresh_tmp', time());

            return true;
        } else {
            $CU->getLastRefresh()->setMarker();
        }

        return false;
    }

    /**
     * Mark single posting as read
     *
     * @param PostingInterface $posting posting
     *
     * @return void
     */
    public function posting(PostingInterface $posting)
    {
        $this->postings[] = $posting;
    }

    /**
     * Mark posting and all subpostings as read
     *
     * @param PostingInterface $posting posting
     * @return void
     */
    public function thread(PostingInterface $posting)
    {
        $postings = $posting->getAllChildren();
        $postings[$posting->get('id')] = $posting;
        $this->postings = array_merge($this->postings, $postings);
    }
}
