<?php

$last = '';
$items = $SaitoEventManager->dispatch(
    'Request.Saito.View.Posting.addForm',
    ['View' => $this]
);
if (!empty($items)) {
    foreach ($items as $item) {
        $last .= $item;
    }
}

['buttons' => $buttons, 'smilies' => $smilies] = $this->Parser->getButtonSet();

$data = [
    'editor' => [
        'buttons' => $buttons,
        'categories' => $categories,
        'smilies' => $smilies,
    ],
    'meta' => [
        'info' => $this->Parser->editorHelp(),
        'last' => $last,
        'quoteSymbol' => $settings['quote_symbol'],
        'autoselectCategory' => $settings['answeringAutoSelectCategory'],
        'subjectMaxLength' => (int)$settings['subject_maxlength'],
    ],
    'posting' => [],
];

if ($isAnswer) {
    $data['meta']['subject'] = $parent->get('subject');
    $data['meta']['text'] = $this->Parser->citeText($parent->get('text'));
}

if ($isEdit) {
    $data['posting'] += [
        'category_id' => $posting->get('category_id'),
        'id' => $posting->get('id'),
        'pid' => $posting->get('pid'),
        'subject' => $posting->get('subject'),
        'text' => $posting->get('text'),
        'time' => $this->TimeH->dateToIso($posting->get('time')),
    ];
}

echo json_encode($data);
