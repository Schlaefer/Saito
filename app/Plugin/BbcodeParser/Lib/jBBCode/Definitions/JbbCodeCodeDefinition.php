<?php

	namespace Plugin\BbcodeParser\Lib\jBBCode\Definitions;

	class CodeWithoutAttributes extends CodeDefinition {

		protected $_sTagName = 'code';

		protected $_sParseContent = false;

		protected function _parse($content, $attributes) {
			$type = 'text';
			if (!empty($attributes['code'])) {
				$type = $attributes['code'];
			}

			$this->Geshi->defaultLanguage = 'text';
			// allow all languages
			$this->Geshi->validLanguages = [true];

			$string = '<div class="geshi-wrapper"><pre lang="' . $type . '">' . $content . '</pre></div>';

			$string = $this->Geshi->highlight($string);
			return $string;
		}

	}

	class CodeWithAttributes extends CodeWithoutAttributes {

		protected $_sUseOptions = true;

	}

