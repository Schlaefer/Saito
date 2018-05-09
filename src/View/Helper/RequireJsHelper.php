<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use Cake\Core\Configure;

/**
 * RequireJS helper
 */
class RequireJsHelper extends AppHelper
{

    public $helpers = [
        'Html',
        'Url'
    ];

    /**
     * Inserts <script> tag for including require.js
     *
     * @param string $dataMain data-main tag start script without .js extension
     * @return string the script-tag
     */
    public function scriptTag(string $dataMain): string
    {
        $jsRoot = Configure::read('App.jsBaseUrl');
        $requireUrl = 'dist/require';

        if (!Configure::read('debug')) {
                $jsRoot = $jsRoot . '/../dist/';
                $requireUrl = $requireUrl . '.min';
        }

        $assetUrlFull = $this->Url->assetUrl($requireUrl, ['ext' => '.js', 'fullBase' => true]);
        // also we need the relative path for the main-script
        $assetUrlRelative = $this->Url->assetUrl(
            $dataMain,
            [
                'pathPrefix' => $jsRoot,
                'ext' => '.js',
                // require.js borks out when used with Cakes timestamp.
                'timestamp' => false,
            ]
        );

        $scriptTag = $this->Html->script($assetUrlFull, ['data-main' => $assetUrlRelative]);

        return $scriptTag;
    }
}
