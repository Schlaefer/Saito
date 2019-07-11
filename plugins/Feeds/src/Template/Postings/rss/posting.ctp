<?php

use Cake\Routing\Router;
use Suin\RSSWriter\Item;

$channel = $this->Feeds->getChannel();
$feed = $this->Feeds->getFeed();

foreach ($entries as $entry) {
    $url = Router::url('/entries/view/' . $entry->get('id'));
    $body = $this->Parser->parse($entry->get('text'), ['return' => 'text']);
    (new Item())
        ->title(html_entity_decode($entry->get('subject'), ENT_NOQUOTES, 'UTF-8'))
        ->description($body)
        ->url($url)
        ->creator($entry->get('name'))
        ->pubDate(strtotime($entry->get('time')))
        ->pubDate($entry->get('time')->getTimestamp())
        ->guid($url, true)
        ->preferCdata(true)
        ->appendTo($channel);
}

echo $feed->render();
