<?php

    $out = [];

foreach ($entries as $entry) {
    $out[] = [
        'id' => (int)$entry['id'],
        'subject' => $entry['subject'],
        'is_nt' => empty($entry['text']),
        'is_pinned' => (bool)$entry['fixed'],
        'time' => $this->TimeH->dateToIso(
            $entry['time']
        ),
        'last_answer' => $this->TimeH->dateToIso(
            $entry['last_answer']
        ),
        'user_id' => (int)$entry['user_id'],
        'user_name' => $entry['user']['username'],
        'category_id' => (int)$entry['category_id'],
        'category_name' => $entry['category']['category']
    ];
}

    echo json_encode($out);
