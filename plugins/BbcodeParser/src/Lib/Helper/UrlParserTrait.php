<?php

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

        if ($truncate === true) {
            $text = $this->_truncate($text);
        }
        $out = "<a href='$url'>$text</a>";

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
     * Truncates long links
     *
     * @bogus does this truncate strings or the longest word in the string or
     *     what?
     * @bogus what about [url=][img]...[/img][url]. Is the [img] url truncated
     *     too?
     *
     * @param string $string string
     *
     * @throws \Exception
     * @return string
     */
    protected function _truncate($string)
    {
        $_textWordMaxLength = $this->_sOptions->get('text_word_maxlength');
        if (empty($_textWordMaxLength)) {
            throw new \Exception('Text word maxlength not set.');
        }

        if (mb_strlen($string) <= $_textWordMaxLength) {
            return $string;
        }

        $_placeholder = ' … ';
        $leftMargin = (int)floor($_textWordMaxLength / 2);
        $rightMargin = (int)(-1 * ($_textWordMaxLength - $leftMargin - mb_strlen($_placeholder)));

        $string = mb_substr($string, 0, $leftMargin) . $_placeholder .
            mb_substr($string, $rightMargin);

        return $string;
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
