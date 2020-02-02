<?php
$out = [
    'id' => $entrySub->get('id'),
    'html' => $this->Posting->renderThread(
        $entrySub,
        ['level' => $level]
    ),
];
echo json_encode($out);
