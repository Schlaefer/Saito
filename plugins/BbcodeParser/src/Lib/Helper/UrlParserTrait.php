<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib\Helper;

use Cake\Validation\Validator;
use Cake\View\Helper\UrlHelper;
use MailObfuscator\View\Helper\MailObfuscatorHelper;
use Saito\DomainParser;

/**
 * @property MailObfuscatorHelper $MailObfuscator
 * @property UrlHelper $Url
 */
trait UrlParserTrait
{
    /**
     * Enforces HTTPS-scheme on URL
     *
     * Applied if:
     * - current host runs on HTTPS
     *
     * @param string $url URL
     * @return string
     */
    protected function _urlToHttps(string $url): string
    {
        if (!env('HTTPS')) {
            return $url;
        }

        return str_replace('http://', 'https://', $url);
    }

    /**
     * Generate email link
     *
     * @param string $url address
     * @param string $text title
     *
     * @return mixed
     */
    protected function _email($url, $text = null)
    {
        if (empty($text)) {
            $text = null;
        }
        $url = str_replace('mailto:', '', $url);

        return $this->MailObfuscator->link($url, $text);
    }

    /**
     * Generate URL
     *
     * @param string $url URL
     * @param string $text title
     * @param bool $label show label
     * @param bool $truncate trunctate
     *
     * @return string
     * @throws \Exception
     */
    protected function _url($url, $text, $label = false, $truncate = false)
    {
        // add http:// to URLs without protocol
        if (strpos($url, '://') === false) {
            // use Cakes Validation class to detect valid URL
            $validator = new Validator();
            $validator->add('url', ['url' => ['rule' => 'url']]);
            $errors = $validator->errors(['url' => $url]);
            if (empty($errors)) {
                $url = 'http://' . $url;
            }
        }

        $out = "<a href='$url' class=\"richtext-link";
        if ($truncate) {
            $out .= ' truncate';
        }
        $out .= "\">$text</a>";
        $out = $this->_decorateTarget($out);

        // add domain info: `[url=domain.info]my link[/url]` -> `my link [domain.info]`
        if ($label !== false && $label !== 'none' && $label !== 'false') {
            if (!empty($url) && preg_match('/\<img\s*?src=/', $text) !== 1) {
                $host = DomainParser::domainAndTld($url);
                if ($host !== null && $host !== env('SERVER_NAME')) {
                    $out .= ' <span class=\'richtext-linkInfo\'>[' . $host . ']</span>';
                }
            }
        }

        return $out;
    }

    /**
     * Adds target="_blank" to *all* external links in arbitrary string $string
     *
     * @param string $string string
     *
     * @return string
     */
    protected function _decorateTarget($string)
    {
        $decorator = function ($matches) {
            $out = '';
            $url = $matches[1];

            // preventing error message for parse_url('http://');
            if (substr($url, -3) === '://') {
                return $matches[0];
            }
            $parsedUrl = parse_url($url);

            if (isset($parsedUrl['host'])) {
                if ($parsedUrl['host'] !== env('SERVER_NAME') && $parsedUrl['host'] !== "www." . env('SERVER_NAME')) {
                    $out = " rel='external' target='_blank'";
                }
            }

            return $matches[0] . $out;
        };

        return preg_replace_callback(
            '#href=["\'](.*?)["\']#is',
            $decorator,
            $string
        );
    }

    /**
     * Creates an URL to an uploaded file based on $id
     *
     * @param string $id currently name in uploads folder
     * @return string URL
     */
    protected function _linkToUploadedFile(string $id) : string
    {
        // @bogus, there's an user-config for that
        $root = '/useruploads/';

        return $this->Url->build($root . $id, ['fullBase' => true]);
    }
}
