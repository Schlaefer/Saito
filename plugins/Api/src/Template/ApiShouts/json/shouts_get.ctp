<?php
    $out = $this->Shouts->prepare($shouts);
    echo json_encode($out);
