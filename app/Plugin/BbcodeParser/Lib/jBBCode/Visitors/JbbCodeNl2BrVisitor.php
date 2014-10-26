<?php

	App::uses('JbbCodeTextVisitor', 'BbcodeParser.Lib/jBBCode/Visitors');

	class JbbCodeNl2BrVisitor extends JbbCodeTextVisitor {

		protected function _processTextNode($text, $node) {
			return nl2br($text);
		}

	}
