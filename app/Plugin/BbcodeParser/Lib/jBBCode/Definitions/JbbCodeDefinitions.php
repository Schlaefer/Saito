<?php

	namespace Plugin\BbcodeParser\Lib\jBBCode\Definitions;

	use Plugin\BbcodeParser\Lib\Helper\Message;
	use Plugin\BbcodeParser\Lib\Helper\UrlParserTrait;
	use Saito\DomainParser;

	include 'CodeDefinition.php';
	include 'JbbHtml5MediaCodeDefinition.php';
	include 'JbbCodeCodeDefinition.php';

	/**
	 * Class Email handles [email]foo@bar.com[/email]
	 *
	 * @package Saito\Jbb\CodeDefinition
	 */
	class Email extends CodeDefinition {

		use UrlParserTrait;

		protected $_sParseContent = false;

		protected $_sTagName = 'email';

		protected function _parse($url, $attributes) {
			return $this->_email($url);
		}

	}

	/**
	 * Class EmailWithAttributes handles [email=foo@bar.com]foobar[/email]
	 *
	 * @package Saito\Jbb\CodeDefinition
	 */
	class EmailWithAttributes extends Email {

		protected $_sUseOptions = true;

		protected function _parse($content, $attributes) {
			return $this->_email($attributes['email'], $content);
		}

	}


	class Embed extends CodeDefinition {

		protected $_sTagName = 'embed';

		protected $_sParseContent = false;

		protected function _parse($content, $attributes) {
			if (empty($this->_sOptions['embedly_enabled'])) {
				return $this->_sHelper->Html->link($content, $content);
			}

			$this->Embedly->setApiKey($this->_sOptions['embedly_key']);
			$embedly = $this->Embedly->embedly($content);
			if ($embedly !== false) {
				return $embedly;
			}

			return __('Embedding failed.');
		}

	}

	class Iframe extends CodeDefinition {

		protected $_sTagName = 'iframe';

		protected $_sParseContent = false;

		protected $_sUseOptions = true;

		/**
		 * Array with domains from which embedding video is allowed
		 *
		 * array(
		 *  'youtube' => 1,
		 *  'vimeo' => 1,
		 * );
		 *
		 * array('*' => 1) means every domain allowed
		 *
		 * @var array
		 */
		protected $_allowedVideoDomains = null;

		protected function _parse($content, $attributes) {
			if (empty($attributes['src'])) {
				return false;
			}

			unset($attributes['iframe']);

			$allowed = $this->_checkHostAllowed($attributes['src']);
			if ($allowed !== true) {
				return $allowed;
			}

			if (strpos($attributes['src'], '?') === false) {
				$attributes['src'] .= '?';
			}
			// @todo @bogus unescaped & in html attribute?
			$attributes['src'] .= '&wmode=Opaque';

			$atrStr = '';
			foreach ($attributes as $attributeName => $attributeValue) {
				$atrStr .= "$attributeName=\"$attributeValue\" ";
			}
			$atrStr = rtrim($atrStr);

			return "<iframe {$atrStr}></iframe>";
		}

		protected function _allowedDomains() {
			if ($this->_allowedVideoDomains !== null) {
				return $this->_allowedVideoDomains;
			}

			// @todo should be done in Setting model
			$ad = explode('|', $this->_sOptions['video_domains_allowed']);
			$trim = function ($v) {
				return trim($v);
			};
			$this->_allowedVideoDomains = array_fill_keys(array_map($trim, $ad), 1);

			return $this->_allowedVideoDomains;
		}

		protected function _checkHostAllowed($url) {
			$allowedDomains = $this->_allowedDomains();
			if (empty($allowedDomains)) {
				return false;
			}

			if ($allowedDomains === ['*' => 1]) {
				return true;
			}

			$host = DomainParser::domain($url);
			if ($host && isset($allowedDomains[$host])) {
				return true;
			}

			$message = sprintf(
				__('Domain <strong>%s</strong> not allowed for embedding video.'),
				$host
			);
			return Message::format($message);
		}

	}

	class Flash extends Iframe {

		protected $_sTagName = 'flash_video';

		protected $_sParseContent = false;

		protected $_sUseOptions = false;

		protected static $_flashVideoDomainsWithHttps = [
			'vimeo' => 1,
			'youtube' => 1
		];

		protected function _parse($content, $attributes) {
			$match = preg_match(
				"#(?P<url>.+?)\|(?P<width>.+?)\|(?<height>\d+)#is",
				$content,
				$matches);
			if (!$match) {
				return Message::format(__('No Flash detected.'));
			}

			$height = $matches['height'];
			$url = $matches['url'];
			$width = $matches['width'];

			$allowed = $this->_checkHostAllowed($url);
			if ($allowed !== true) {
				return $allowed;
			}

			if (env('HTTPS')) {
				$host = DomainParser::domain($url);
				if (isset(self::$_flashVideoDomainsWithHttps[$host])) {
					$url = str_ireplace('http://', 'https://', $url);
				}
			}

			$out = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="' . $width . '" height="' . $height . '">
									<param name="movie" value="' . $url . '"></param>
									<embed src="' . $url . '" width="' . $width . '" height="' . $height . '" type="application/x-shockwave-flash" wmode="opaque" style="width:' . $width . 'px; height:' . $height . 'px;" id="VideoPlayback" flashvars=""> </embed> </object>';
			return $out;
		}

	}

	class Image extends CodeDefinition {

		protected $_sTagName = 'img';

		protected $_sParseContent = false;

		protected function _parse($url, $attributes) {
			// process [img=(parameters)]
			$options = [];
			if (!empty($attributes['img'])) {
				$default = trim($attributes['img']);
				switch ($default) {
					case 'left':
						$options['style'] = 'float: left;';
						break;
					case 'right':
						$options['style'] = 'float: right;';
						break;
					default:
						preg_match('/(\d{0,3})(?:x(\d{0,3}))?/i', $default, $dimension);
						// $dimension for [img=50] or [img=50x100]
						// [0] (50) or (50x100)
						// [1] (50)
						// [2] (100)
						if (!empty($dimension[1])) {
							$options['width'] = $dimension[1];
							if (!empty($dimension[2])) {
								$options['height'] = $dimension[2];
							}
						}
				}
			}

			return $this->Html->image($url, $options);
		}

	}

	class ImageWithAttributes extends Image {

		protected $_sUseOptions = true;

	}

	/**
	 * Class UlList handles [list][*]…[/list]
	 *
	 * @see https://gist.github.com/jbowens/5646994
	 * @package Saito\Jbb\CodeDefinition
	 */
	class UlList extends CodeDefinition {

		protected $_sTagName = 'list';

		protected function _parse($content, $attributes) {
			$listPieces = explode('[*]', $content);
			unset($listPieces[0]);
			$listPieceProcessor = function ($li) {
				return '<li>' . $li . '</li>' . "\n";
			};
			$listPieces = array_map($listPieceProcessor, $listPieces);

			return '<ul>' . implode('', $listPieces) . '</ul>';
		}

	}

	class Spoiler extends CodeDefinition {

		protected $_sTagName = 'spoiler';

		protected function _parse($content, $attributes) {
			$length = mb_strlen(strip_tags($content));
			$minLenght = mb_strlen(__('Spoiler')) + 4;
			if ($length < $minLenght) {
				$length = $minLenght;
			}

			$title = $this->_mbStrpad(
				' ' . __('Spoiler') . ' ',
				$length,
				'▇',
				STR_PAD_BOTH
			);

			$json = json_encode(['string' => $content]);
			$id = 'spoiler_' . rand(0, 9999999999999);

			$out = <<<EOF
<div class="richtext-spoiler" style="display: inline;">
	<script>
		window.$id = $json;
	</script>
	<a href="#" class="richtext-spoiler-link"
		onclick='this.parentNode.innerHTML = window.$id.string; delete window.$id; return false;'
		>
		$title
	</a>
</div>
EOF;
			return $out;
		}

		/**
		 * @see http://www.php.net/manual/en/function.str-pad.php#111147
		 */
		protected function _mbStrpad($str, $padLen, $padStr = ' ', $dir = STR_PAD_RIGHT) {
			$strLen = mb_strlen($str);
			$padStrLen = mb_strlen($padStr);
			if (!$strLen && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
				$strLen = 1; // @debug
			}
			if (!$padLen || !$padStrLen || $padLen <= $strLen) {
				return $str;
			}

			$result = null;
			$repeat = ceil($strLen - $padStrLen + $padLen);
			if ($dir == STR_PAD_RIGHT) {
				$result = $str . str_repeat($padStr, $repeat);
				$result = mb_substr($result, 0, $padLen);
			} else {
				if ($dir == STR_PAD_LEFT) {
					$result = str_repeat($padStr, $repeat) . $str;
					$result = mb_substr($result, -$padLen);
				} else {
					if ($dir == STR_PAD_BOTH) {
						$length = ($padLen - $strLen) / 2;
						$repeat = ceil($length / $padStrLen);
						$result = mb_substr(str_repeat($padStr, $repeat), 0,
								floor($length)) .
							$str .
							mb_substr(str_repeat($padStr, $repeat), 0, ceil($length));
					}
				}
			}

			return $result;
		}

	}

	class Upload extends CodeDefinition {

		protected $_sTagName = 'upload';

		protected $_sParseContent = false;

		protected function _parse($content, $attributes) {
			$this->FileUpload->reset();
			$params = $this->_getUploadParams($attributes);
			return $this->FileUpload->image($content, $params);
		}

		protected function _getUploadParams($attributes) {
			return [];
		}

	}

	class UploadWithAttributes extends Upload {

		protected $_sUseOptions = true;

		protected function _getUploadParams($attributes) {
			if (empty($attributes)) {
				return [];
			}

			$_allowedKeys = array_fill_keys(['width', 'height'], false);
			$_allowedAttributes = array_intersect_key($attributes, $_allowedKeys);
			$params = [
					'autoResize' => false,
					'resizeThumbOnly' => false,
				] + $_allowedAttributes;
			return $params;
		}

	}

	/**
	 * Class Url handles [url]http://example.com[/url]
	 *
	 * @package Saito\Jbb\CodeDefinition
	 */
	class Url extends CodeDefinition {

		use UrlParserTrait;

		protected $_sParseContent = false;

		protected $_sTagName = 'url';

		protected function _parse($url, $attributes) {
			$defaults = ['label' => true];
			// parser may return $attributes = null
			if (empty($attributes)) {
				$attributes = [];
			}
			$attributes = $attributes + $defaults;
			return $this->_getUrl($url, $attributes);
		}

		protected function _getUrl($content, $attributes) {
			$shortTag = true;
			return $this->_url($content, $content, $attributes['label'], $shortTag);
		}

	}

	/**
	 * Class Link handles [link]http://example.com[/link]
	 *
	 * @package Saito\Jbb\CodeDefinition
	 */
	class Link extends Url {

		protected $_sTagName = 'link';

	}

	/**
	 * Class UrlWithAttributes handles [url=http://example.com]foo[/url]
	 *
	 * @package Saito\Jbb\CodeDefinition
	 */
	class UrlWithAttributes extends Url {

		protected $_sParseContent = true;

		protected $_sUseOptions = true;

		protected function _getUrl($content, $attributes) {
			$shortTag = false;
			$url = $attributes[$this->_sTagName];
			return $this->_url($url, $content, $attributes['label'], $shortTag);
		}

	}

	/**
	 * Class LinkWithAttributes handles [link=http://example.com]foo[/link]
	 *
	 * @package Saito\Jbb\CodeDefinition
	 */
	class LinkWithAttributes extends UrlWithAttributes {

		protected $_sTagName = 'link';

	}
