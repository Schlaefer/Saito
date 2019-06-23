<?php

namespace Feeds\View\Helper;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\View\Helper;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;

class FeedsHelper extends Helper
{
    /** @var Feed */
    private $feed;

    /** @var Channel */
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
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * Get RssWriter feed
     *
     * @return Channel
     */
    public function getFeed(): Feed
    {
        return $this->feed;
    }
}
