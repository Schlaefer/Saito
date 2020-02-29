<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace ImageUploader\Lib;

use Cake\Routing\Router;
use Plugin\BbcodeParser\src\Lib\Helper\UploadUrlInterface;

class UploadUrlLocal implements UploadUrlInterface
{
    /**
     * Base URL in Cake installation
     * @var string
     */
    protected string $baseUrl;

    /**
     * Constructor
     * @param string $baseUrl base-URL
     * @return void
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = '/' . trim($baseUrl, '/') . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $path): string
    {
        return Router::url($this->baseUrl . $path, true);
    }
}
