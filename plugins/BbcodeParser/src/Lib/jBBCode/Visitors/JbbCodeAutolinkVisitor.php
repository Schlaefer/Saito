<?php

namespace Plugin\BbcodeParser\src\Lib\jBBCode\Visitors;

use Plugin\BbcodeParser\src\Lib\Helper\UrlParserTrait;

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
        $string = $this->_hashLink($string);
        $string = $this->_atUserLink($string);

        return $this->_autolink($string);
    }

    /**
     * {@inheritDoc}
     */
    protected function _atUserLink($string)
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
        $users = $this->_sOptions['UserList']->get();
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
        $baseUrl = $this->_sOptions['webroot'] . $this->_sOptions['atBaseUrl'];
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
     * autolink
     *
     * @param string $string string
     *
     * @return string
     */
    protected function _autolink($string)
    {
        $replace = function ($matches) {
            // exclude punctuation at end of sentence from URLs
            $ignoredEndChars = implode('|', [',', '\?', ',', '\.', '\)', '!']);
            preg_match(
                '/(?P<element>.*?)(?P<suffix>' . $ignoredEndChars . ')?$/',
                $matches['element'],
                $m
            );
            // keep ['element'] and ['suffix'] and include ['prefix']
            $matches = $m + $matches;

            if (strpos($matches['element'], '://') === false) {
                $matches['element'] = 'http://' . $matches['element'];
            }
            $matches += [
                'prefix' => '',
                'suffix' => ''
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
     * Hash link
     *
     * @param string $string string
     *
     * @return string
     */
    protected function _hashLink($string)
    {
        $baseUrl = $this->_sOptions['webroot'] . $this->_sOptions['hashBaseUrl'];
        $string = preg_replace_callback(
            '/(?<=\s|^|])(?<tag>#)(?<element>\d+)(?!\w)/',
            function ($m) use ($baseUrl) {
                $hash = $m['element'];

                return $this->_url($baseUrl . $hash, '#' . $hash);
            },
            $string
        );

        return $string;
    }
}
