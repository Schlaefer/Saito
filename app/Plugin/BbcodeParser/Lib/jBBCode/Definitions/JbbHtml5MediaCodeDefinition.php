<?php

	namespace Plugin\BbcodeParser\Lib\jBBCode\Definitions;

	use Plugin\BbcodeParser\Lib\Helper\Message;

	abstract class Html5Media extends CodeDefinition {

		protected $_sParseContent = false;

		protected static $_html5AudioExtensions = array(
			'm4a',
			'ogg',
			'opus',
			'mp3',
			'wav',
		);

		protected function _audio($content) {
			$out = "<audio src='$content' controls='controls'>";
			$out .= Message::format(__(
				'Your browser does not support HTML5 audio. Please updgrade to a modern ' .
				'browser. In order to watch this stream you need an HTML5 capable browser.',
				true));
			$out .= "</audio>";
			return $out;
		}

		protected function _video($content) {
			// fix audio files mistakenly wrapped into an [video] tag
			if (preg_match('/(' . implode('|', self::$_html5AudioExtensions) . ')$/i',
					$content) === 1
			) {
				return $this->_audio($content);
			}

			$out = "<video src='$content' controls='controls' x-webkit-airplay='allow'>";
			$out .= Message::format(__(
				'Your browser does not support HTML5 video. Please updgrade to a modern ' .
				'browser. In order to watch this stream you need an HTML5 capable browser.',
				true));
			$out .= '</video>';
			return $out;
		}

	}

	class Html5Audio extends Html5Media {

		protected $_sTagName = 'audio';

		protected function _parse($content, $attributes) {
			return $this->_audio($content);
		}

	}

	class Html5Video extends Html5Media {

		protected $_sTagName = 'video';

		protected function _parse($content, $attributes) {
			return $this->_video($content);
		}

	}

