[
<?php
    $out = [];
foreach ($entries as $entry) :
    $out[] = $this->element('Api.entry', ['entry' => $entry]);
endforeach;
    echo implode(',', $out);
?>
]