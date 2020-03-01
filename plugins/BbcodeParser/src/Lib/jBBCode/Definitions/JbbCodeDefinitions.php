<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace BbcodeParser\Lib\jBBCode\Definitions;

use Cake\Cache\Cache;
use BbcodeParser\Lib\Helper\Message;
use BbcodeParser\Lib\Helper\UrlParserTrait;
use Saito\DomainParser;

/**
 * Class Email handles [email]foo@bar.com[/email]
 *
 * @package Saito\Jbb\CodeDefinition
 */
//@codingStandardsIgnoreStart
class Email extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    use UrlParserTrait;

    protected $_sParseContent = false;

    protected $_sTagName = 'email';

    /**
     * {@inheritDoc}
     */
    protected function _parse($url, $attributes, \JBBCode\ElementNode $node)
    {
        return $this->_email($url);
    }
}

/**
 * Class EmailWithAttributes handles [email=foo@bar.com]foobar[/email]
 *
 * @package Saito\Jbb\CodeDefinition
 */
//@codingStandardsIgnoreStart
class EmailWithAttributes extends Email
//@codingStandardsIgnoreEnd
{
    protected $_sUseOptions = true;

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        return $this->_email($attributes['email'], $content);
    }
}

//@codingStandardsIgnoreStart
class Embed extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'embed';

    protected $_sParseContent = false;

    /**
     * {@inheritDoc}
     */
    protected function _parse($url, $attributes, \JBBCode\ElementNode $node)
    {
        if (!$this->_sOptions->get('content_embed_active')) {
            if (!$this->_sOptions->get('autolink')) {
                return $url;
            }

            return $this->Html->link($url, $url, ['target' => '_blank']);
        }

        $loader = function () use ($url) {
            $embed = ['url' => $url];

            try {
                $info = \Embed\Embed::create(
                    $url,
                    [
                    'min_image_width' => 100,
                    'min_image_height' => 100,
                    ]
                );

                $embed = [
                    'html' => $info->code,
                    'providerIcon' => $info->providerIcon,
                    'providerName' => $info->providerName,
                    'providerUrl' => $info->providerUrl,
                    'title' => $info->title,
                    'url' => $info->url ?? $url,
                ];

                if ($this->_sOptions->get('content_embed_text')) {
                    $embed['description'] = $info->description;
                }

                if ($this->_sOptions->get('content_embed_media')) {
                    $embed['image'] = $info->image;
                }
            } catch (\Throwable $e) {
            }

            return $embed;
        };

        $callable = \Closure::fromCallable($loader);

        $uid = 'embed-' . md5($url);
        $info = Cache::remember($uid, $callable, 'bbcodeParserEmbed');

        return $this->_sHelper->Html->div('js-embed', '', ['id' => $uid, 'data-embed' => json_encode($info)]);
    }
}

//@codingStandardsIgnoreStart
class Iframe extends CodeDefinition
//@codingStandardsIgnoreEnd
{
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

    /**
     * {@inheritDoc}
     */
    protected function _parse($url, $attributes, \JBBCode\ElementNode $node)
    {
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
        $attributes['src'] .= '&amp;wmode=Opaque';

        $atrStr = '';
        foreach ($attributes as $attributeName => $attributeValue) {
            $atrStr .= "$attributeName=\"$attributeValue\" ";
        }
        $atrStr = rtrim($atrStr);

        $html = <<<eof
<div class="embed-responsive embed-responsive-16by9">
    <iframe class="embed-responsive-item" {$atrStr}></iframe>
</div>
eof;

        return $html;
    }

    /**
     * get allowed domains
     *
     * @return array
     */
    protected function _allowedDomains()
    {
        if ($this->_allowedVideoDomains !== null) {
            return $this->_allowedVideoDomains;
        }

        $ad = explode('|', $this->_sOptions->get('video_domains_allowed'));
        $trim = function ($v) {
            return trim($v);
        };
        $this->_allowedVideoDomains = array_fill_keys(array_map($trim, $ad), 1);

        return $this->_allowedVideoDomains;
    }

    /**
     * Check host allowed
     *
     * @param string $url url
     *
     * @return bool|string
     */
    protected function _checkHostAllowed($url)
    {
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

//@codingStandardsIgnoreStart
class Flash extends Iframe
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'flash_video';

    protected $_sParseContent = false;

    protected $_sUseOptions = false;

    protected static $_flashVideoDomainsWithHttps = [
        'vimeo' => 1,
        'youtube' => 1,
    ];

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        $match = preg_match(
            "#(?P<url>.+?)\|(?P<width>.+?)\|(?<height>\d+)#is",
            $content,
            $matches
        );
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

        $out = <<<EOF
<object
    classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
    width="$width"
    height="$height">
        <param name="movie" value="$url"></param>
        <embed src="$url"
            width="$width"
            height="$height"
            type="application/x-shockwave-flash"
            wmode="opaque"
            style="width:${width}px; height:${height}px;"
            id="VideoPlayback"
            flashvars="">
        </embed>
</object>
EOF;

        return $out;
    }
}

//@codingStandardsIgnoreStart
class FileWithAttributes extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    use UrlParserTrait;

    protected $_sTagName = 'file';

    protected $_sParseContent = false;

    protected $_sUseOptions = true;

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        if (empty($attributes['src']) || $attributes['src'] !== 'upload') {
            $message = sprintf(__('File not allowed.'));

            return Message::format($message);
        }

        $url = $this->_linkToUploadedFile($content);

        return $this->_sHelper->Html->link($content, $url, ['target' => '_blank']);
    }
}

//@codingStandardsIgnoreStart
class Image extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    use UrlParserTrait;

    protected $_sTagName = 'img';

    protected $_sParseContent = false;

    /**
     * {@inheritDoc}
     */
    protected function _parse($url, $attributes, \JBBCode\ElementNode $node)
    {
        // image is internaly uploaded
        if (!empty($attributes['src']) && $attributes['src'] === 'upload') {
            $url = $this->_linkToUploadedFile($url);
        }

        // process [img=(parameters)]
        $options = [];
        if (!empty($attributes['img'])) {
            $default = trim($attributes['img']);
            switch ($default) {
                default:
                    preg_match(
                        '/(\d{0,3})(?:x(\d{0,3}))?/i',
                        $default,
                        $dimension
                    );
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

        $url = $this->_urlToHttps($url);
        $image = $this->Html->image($url, $options);

        if ($node->getParent()->getTagName() === 'Document') {
            $image = $this->_sHelper->Html->link(
                $image,
                $url,
                ['escape' => false, 'target' => '_blank']
            );
        }

        return $image;
    }
}

//@codingStandardsIgnoreStart
class ImageWithAttributes extends Image
//@codingStandardsIgnoreEnd
{
    protected $_sUseOptions = true;
}

/**
 * Class UlList handles [list][*]…[/list]
 *
 * @see https://gist.github.com/jbowens/5646994
 * @package Saito\Jbb\CodeDefinition
 */
//@codingStandardsIgnoreStart
class UlList extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'list';

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        $listPieces = explode('[*]', $content);
        unset($listPieces[0]);
        $listPieceProcessor = function ($li) {
            return '<li>' . $li . '</li>' . "\n";
        };
        $listPieces = array_map($listPieceProcessor, $listPieces);

        return '<ul>' . implode('', $listPieces) . '</ul>';
    }
}

//@codingStandardsIgnoreStart
class Spoiler extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'spoiler';

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
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
     * Strpad
     *
     * @see http://www.php.net/manual/en/function.str-pad.php#111147
     *
     * @param string $str string
     * @param int $padLen length
     * @param string $padStr padding
     * @param int $dir direction
     *
     * @return null|string
     */
    protected function _mbStrpad(
        $str,
        $padLen,
        $padStr = ' ',
        $dir = STR_PAD_RIGHT
    ) {
        $strLen = mb_strlen($str);
        $padStrLen = mb_strlen($padStr);
        if (!$strLen && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
            $strLen = 1; // @debug
        }
        if (!$padLen || !$padStrLen || $padLen <= $strLen) {
            return $str;
        }

        $result = null;
        $repeat = (int)ceil($strLen - $padStrLen + $padLen);
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
                    $repeat = (int)ceil($length / $padStrLen);
                    $result = mb_substr(str_repeat($padStr, $repeat), 0, (int)floor($length)) .
                        $str .
                        mb_substr(str_repeat($padStr, $repeat), 0, (int)ceil($length));
                }
            }
        }

        return $result;
    }
}

/**
 * Hanldes [upload]<image>[/upload]
 *
 * @deprecated since Saito 5.2; kept for backwards compatability
 */
//@codingStandardsIgnoreStart
class Upload extends Image
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'upload';

    /**
     * {@inheritDoc}
     */
    protected function _parse($content, $attributes, \JBBCode\ElementNode $node)
    {
        $attributes['src'] = 'upload';
        if (!empty($attributes['width'])) {
            $attributes['img'] = $attributes['width'];
        }
        if (!empty($attributes['height'])) {
            $attributes['img'] .= 'x' . $attributes['height'];
        }

        return parent::_parse($content, $attributes, $node);
    }
}

/**
 * Hanldes [upload width=<width> height=<height>]<image>[/upload]
 *
 * @deprecated since Saito 5.2; kept for backwards compatability
 */
//@codingStandardsIgnoreStart
class UploadWithAttributes extends Upload
//@codingStandardsIgnoreEnd
{
    protected $_sUseOptions = true;
}

/**
 * Class Url handles [url]http://example.com[/url]
 *
 * @package Saito\Jbb\CodeDefinition
 */
//@codingStandardsIgnoreStart
class Url extends CodeDefinition
//@codingStandardsIgnoreEnd
{
    use UrlParserTrait;

    protected $_sParseContent = false;

    protected $_sTagName = 'url';

    /**
     * {@inheritDoc}
     */
    protected function _parse($url, $attributes, \JBBCode\ElementNode $node)
    {
        $defaults = ['label' => true];
        // parser may return $attributes = null
        if (empty($attributes)) {
            $attributes = [];
        }
        $attributes = $attributes + $defaults;

        return $this->_getUrl($url, $attributes);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getUrl($content, $attributes)
    {
        $shortTag = true;

        return $this->_url($content, $content, $attributes['label'], $shortTag);
    }
}

/**
 * Class Link handles [link]http://example.com[/link]
 *
 * @package Saito\Jbb\CodeDefinition
 */
//@codingStandardsIgnoreStart
class Link extends Url
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'link';
}

/**
 * Class UrlWithAttributes handles [url=http://example.com]foo[/url]
 *
 * @package Saito\Jbb\CodeDefinition
 */
//@codingStandardsIgnoreStart
class UrlWithAttributes extends Url
//@codingStandardsIgnoreEnd
{
    protected $_sParseContent = true;

    protected $_sUseOptions = true;

    /**
     * {@inheritDoc}
     */
    protected function _getUrl($content, $attributes)
    {
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
//@codingStandardsIgnoreStart
class LinkWithAttributes extends UrlWithAttributes
//@codingStandardsIgnoreEnd
{
    protected $_sTagName = 'link';
}
