<?php

namespace Saito;

class DomainParser
{

    /**
     * Returns host name for $uri
     *
     * `http://www.youtube.com/foo` returns `youtube`
     *
     * @param string $uri uri
     * @return string|bool domain if detected of false otherwise
     */
    public static function domain($uri)
    {
        return self::domainAndTld($uri, 'domain');
    }

    /**
     * Returns top level domain
     *
     * @param string $uri uri
     * @param string $part part
     * @return string|bool requested URI part if detected or false otherwise
     */
    public static function domainAndTld($uri, $part = 'fulldomain')
    {
        //@codingStandardsIgnoreStart
        $host = @parse_url($uri, PHP_URL_HOST);
        //@codingStandardsIgnoreEnd
        if (!empty($host) && $host !== false) {
            $found = preg_match(
                '/(?P<fulldomain>(?P<domain>[a-z0-9][a-z0-9\-]{1,63})\.(?<tld>[a-z\.]{2,6}))$/i',
                $host,
                $regs
            );
            if ($found && !empty($regs[$part])) {
                return $regs[$part];
            }
        }

        return false;
    }
}
