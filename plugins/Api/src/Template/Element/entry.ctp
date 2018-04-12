<?php
    $out = [
        'id' => (int)$entry['id'],
        'parent_id' => (int)$entry['pid'],
        'thread_id' => (int)$entry['tid'],
        'subject' => $entry['subject'],
        'is_nt' => empty($entry['text']),
        'time' => $this->Api->mysqlTimestampToIso(
            $entry['time']
        ),
        'last_answer' => $this->Api->mysqlTimestampToIso(
            $entry['last_answer']
        ),
        'text' => $entry['text'],
        'html' => $this->Parser->parse(
            $entry['text'],
            ['multimedia' => true, 'wrap' => false]
        ),
        'user_id' => (int)$entry['user_id'],
        'user_name' => $entry['user']['username'],
        'edit_name' => $entry['edited_by'],
        'edit_time' => $this->Api->mysqlTimestampToIso($entry['edited']),
        'category_id' => (int)$entry['category_id'],
        'category_name' => $entry['category']['category']
    ];

    if ($CurrentUser->isLoggedIn()) {
        $out += [
            'is_locked' => $entry['locked'] != false,
        ];
    }
    echo json_encode($out);
