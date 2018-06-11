<?php
$out = [
    'type' => 'postingviews',
    'id' => 999999999999,
    'attributes' => [
        'id' => 999999999999,
        'category_id' => $posting->get('category_id'),
        'subject' => $posting->get('subject'),
        'text' => $posting->get('text'),
        'html' => $this->element(
            '/entry/view_content',
            ['entry' => $posting, 'level' => 0]
        )
    ],
];

echo json_encode($out);
