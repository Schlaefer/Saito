<?php
    $out = [
        'id' => $id,
        'last_refresh' => $this->TimeH->dateToIso($last_refresh)
    ];
    echo json_encode($out);
