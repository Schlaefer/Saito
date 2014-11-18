<?php
$this->set('documentData', array(
    'xmlns:dc' => 'http://purl.org/dc/elements/1.1/'
));

$this->set('channelData', [
    'title' => $title,
    'link' => $this->Url->build('/', true),
    'description' => $title,
    'language' => $language
]);

foreach ($entries as $entry) {
    $postTime = strtotime($entry->get('time'));

    $postLink = [
        'controller' => 'entries',
        'action' => 'view',
        $entry->get('id')
    ];

    $bodyText = '';
    $bodyText = $this->Parser->parse($entry->get('text'), ['return' => 'text']);
    /*
    // You should import Sanitize
    App::import('Sanitize');
    // This is the part where we clean the body text for output as the description
    // of the rss item, this needs to have only text to make sure the feed validates
    $bodyText = preg_replace('=\(.*?\)=is', '', $entry['Entry']['text']);
    $bodyText = $this->Text->stripLinks($bodyText);
    $bodyText = Sanitize::stripAll($bodyText);
    $bodyText = $this->Text->truncate($bodyText, 400, array(
            'ending' => '...',
            'exact'  => true,
            'html'   => true,
    ));
     *
     */

    echo $this->Rss->item(
        [
            'namespace' => [
                'prefix' => 'dc',
                'url' => 'http://purl.org/dc/elements/1.1/'
            ]
        ],
        [
            'title' => html_entity_decode($entry->get('subject'),
                ENT_NOQUOTES, 'UTF-8'),
            'link' => $postLink,
            'guid' => ['url' => $postLink, 'isPermaLink' => 'true'],
            'description' => ['value' => $bodyText],
            'dc:creator' => $entry->get('username'),
            'pubDate' => $postTime,
        ]
    );
}
