<?php
$out = $entry->get('subject');
if (!$entry->isNt()) {
    $out .= "\n\n" . $entry->get('text');
}
echo $this->Html->tag('pre', $out, ['style' => 'white-space: pre-wrap']);
