<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito;

use LayerShifter\TLDExtract\Extract;

class DomainParser
{
    /**
     * Returns host name for $uri
     *
     * `http://www.youtube.com/foo` returns `youtube`
     *
     * @param string $uri uri
     * @return string|null domain if detected or null otherwise
     */
    public static function domain(string $uri): ?string
    {
        return (new Extract())->parse($uri)->getHostname();
    }

    /**
     * Returns top level domain
     *
     * `http://www.youtube.com/foo` returns `youtube.com`
     *
     * @param string $uri uri
     * @return string|null requested URI part if detected or null otherwise
     */
    public static function domainAndTld(string $uri): ?string
    {
        return (new Extract())->parse($uri)->getRegistrableDomain();
    }
}
