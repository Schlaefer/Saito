<?php

	App::uses('ThreadHtmlRenderer', 'Lib/Thread/Renderer');

	class MixHtmlRenderer extends ThreadHtmlRenderer {

		protected function _renderCore($entry, $level, $node) {
			$id = $node->id;
			$css = $this->_generateEntryTypeCss($level, $node->isNew(), $id);

			if ($node->isIgnored()) {
				$css .= ' ignored';
			}

			$element = $this->_EntryHelper->_View->element('/entry/view_posting',
				['entry' => $entry, 'level' => $level]);

			$out = <<<EOF
<li id="{$id}" class="{$css}">
	<div class="mixEntry panel">
		{$element}
	</div>
</li>
EOF;

			return $out;
		}

	}