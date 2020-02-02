<?php

$out = [
    'data' => $this->ImageUploader->image($image),
];

echo json_encode($out);
