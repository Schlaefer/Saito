<?php
declare(strict_types=1);

$out = [];

foreach ($bookmarks as $bookmark) {
    $posting = $bookmark->get('entry')->toPosting()->withCurrentUser($CurrentUser);
    $threadLineHtml = $this->Posting->renderThread(
        $posting,
        ['rootWrap' => true]
    );
    $out[] = [
        'id' => $bookmark->get('id'),
        'type' => 'bookmarks',
        'attributes' => [
            'id' => $bookmark->get('id'),
            'comment' => $bookmark->get('comment'),
            'entry_id' => $bookmark->get('entry_id'),
            'threadline_html' => $threadLineHtml,
            'user_id' => $bookmark->get('user_id'),
        ],
    ];
}

echo json_encode(['data' => $out]);
