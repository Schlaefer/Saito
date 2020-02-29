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

interface UploadUrlInterface
{
    /**
     * Build URL linking to uploaded file
     * @param string $path Path (id/name) of the upload
     * @return string URL
     */
    public function build(string $path): string;
}
