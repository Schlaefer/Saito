<?php

$out = [];
foreach ($images as $image) {
    $out[] = $this->ImageUploader->image($image);
}
echo json_encode(['data' => $out]);
