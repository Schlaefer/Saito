<?php

	App::uses('JbbCodeTextVisitor', 'Lib/Bbcode/jBBCode/Visitors');

	class JbbCodeNl2BrVisitor extends JbbCodeTextVisitor {

		protected function _processTextNode($text) {
			return nl2br($text);
		}

	}
