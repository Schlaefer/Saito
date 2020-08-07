<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Visitors;

use Plugin\BbcodeParser\src\Lib\Helper\UrlParserTrait;

/**
 * Handles all implicit linking in a text (autolink URLs, tags, ...)
 */
class JbbCodeAutolinkVisitor extends JbbCodeTextVisitor
{
    use UrlParserTrait;

    protected $_disallowedTags = ['code'];

    /**
     * {@inheritDoc}
     */
    protected function _processTextNode($string, $node)
    {
        // don't auto-link in url tags; problem is that 'urlWithAttributes' definition
        // reuses 'url' tag with ParseContent = true
        if ($node->getParent()->getTagName() === 'url') {
            return $string;
        }
        $string = $this->hashLink($string);
        $string = $this->atUserLink($string);

        return $this->autolink($string);
    }

    /**
     * Links @<username> to the user's profile.
     *
     * @param string $string The Text to be parsed.
     * @return string The text with usernames linked.
     */
    protected function atUserLink(string $string): string
    {
        $tags = [];

        /*
         * - '\pP' all unicode punctuation marks
         * - '<' if nl2br has taken place whatchout for <br /> linebreaks
         */
        $hasTags = preg_match_all('/(\s|^)@([^\s\pP<]+)/m', $string, $tags);
        if (!$hasTags) {
            return $string;
        }

        // would be cleaner to pass userlist by value, but for performance reasons
        // we only query the db if we actually have any @ tags
        $users = $this->_sOptions->get('UserList')->get();
        sort($users);
        $names = [];
        if (empty($tags[2]) === false) {
            $tags = $tags[2];
            foreach ($tags as $tag) {
                if (in_array($tag, $users)) {
                    $names[$tag] = 1;
                } else {
                    $continue = 0;
                    foreach ($users as $user) {
                        if (mb_strpos($user, $tag) === 0) {
                            $names[$user] = 1;
                            $continue = true;
                        }
                        if ($continue === false) {
                            break;
                        } elseif ($continue !== 0) {
                            $continue = false;
                        }
                    }
                }
            }
        }
        krsort($names);
        $baseUrl = $this->_sOptions->get('webroot') . $this->_sOptions->get('atBaseUrl');
        foreach ($names as $name => $v) {
            $title = urlencode($name);
            $link = $this->_url(
                $baseUrl . $title,
                "@$name",
                false
            );
            $string = str_replace("@$name", $link, $string);
        }

        return $string;
    }

    /**
     * Autolinks URLs not surrounded by explicit URL-tags for user-convenience.
     *
     * @param string $string The text to be parsed for URLs.
     * @return string The text with URLs linked.
     */
    protected function autolink(string $string): string
    {
        $replace = function (array $matches): string {
            /// don't link locally
            if (strpos($matches['element'], 'file://') !== false) {
                return $matches['element'];
            }

            /// exclude punctuation at end of sentence from URLs
            $ignoredEndChars = implode('|', [',', '\?', ',', '\.', '\)', '!']);
            preg_match(
                '/(?P<element>.*?)(?P<suffix>' . $ignoredEndChars . ')?$/',
                $matches['element'],
                $m
            );

            /// exclude ignored end chars if paired in URL foo.com/bar_(baz)
            if (!empty($m['suffix'])) {
                $ignoredIfNotPaired = [
                    ['open' => '(', 'close' => ')'],
                ];
                foreach ($ignoredIfNotPaired as $pair) {
                    $isUnpaired = substr_count($m['element'], $pair['open']) > substr_count($m['element'], $pair['close']);
                    if ($isUnpaired) {
                        $m['element'] .= $m['suffix'];
                        unset($m['suffix']);
                    }
                }
            }

            /// keep ['element'] and ['suffix'] and include ['prefix']; (array) for phpstan
            $matches = (array)($m + $matches);

            if (strpos($matches['element'], '://') === false) {
                $matches['element'] = 'http://' . $matches['element'];
            }
            $matches += [
                'prefix' => '',
                'suffix' => '',
            ];

            $url = $this->_url(
                $matches['element'],
                $matches['element'],
                false,
                true
            );

            return $matches['prefix'] . $url . $matches['suffix'];
        };

        //# autolink http://urls
        $string = preg_replace_callback(
            "#(?<=^|[\n (])(?P<element>[\w]+?://.*?[^ \"\n\r\t<]*)#is",
            $replace,
            $string
        );

        //# autolink without http://, i.e. www.foo.bar/baz
        $string = preg_replace_callback(
            "#(?P<prefix>^|[\n (])(?P<element>(www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:/[^ \"\t\n\r<]*)?)#is",
            $replace,
            $string
        );

        //# autolink email
        $string = preg_replace_callback(
            "#(?<=^|[\n ])(?P<content>([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+))#i",
            function ($matches) {
                return $this->_email($matches['content']);
            },
            $string
        );

        return $string;
    }

    /**
     * Links #<posting-ID> to that posting.
     *
     * @param string $string Text to be parsed for #<id>.
     * @return string Text containing hash-links.
     */
    protected function hashLink(string $string): string
    {
        $baseUrl = $this->_sOptions->get('webroot') . $this->_sOptions->get('hashBaseUrl');
        $string = preg_replace_callback(
            '/(?<=\s|^|]|\()(?<tag>#)(?<element>\d+)(?!\w)/',
            function (array $m) use ($baseUrl): string {
                $hash = $m['element'];

                return $this->_url($baseUrl . $hash, '#' . $hash);
            },
            $string
        );

        return $string;
    }
}
