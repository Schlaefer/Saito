<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Feeds\View\Helper;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\View\Helper;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;

class FeedsHelper extends Helper
{
    /**
     * @var \Suin\RSSWriter\Feed
     */
    private $feed;

    /**
     * @var \Suin\RSSWriter\Channel
     */
    private $channel;

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event, $viewFile)
    {
        $this->channel = new Channel();
        $this->feed = new Feed();

        $url = Router::url('/', true);
        $language = Configure::read('Saito.language');

        $this->channel
            ->title($this->getView()->get('titleForLayout'))
            ->url($url)
            ->feedUrl($this->getView()->getRequest()->getRequestTarget())
            ->language($language)
            ->appendTo($this->feed);
    }

    /**
     * Get RssWriter channel
     *
     * @return \Suin\RSSWriter\Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * Get RssWriter feed
     *
     * @return \Suin\RSSWriter\Feed
     */
    public function getFeed(): Feed
    {
        return $this->feed;
    }
}
