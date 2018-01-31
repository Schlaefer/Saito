<?php

namespace App\View\Helper;

use Cake\Core\Configure;

class RequireJsHelper extends AppHelper
{

    public $helpers = [
        'Html',
        'Js',
        'Url'
    ];

    /**
     * url to require.js relative app/webroot/js
     *
     * @var array
     */
    protected $_requireUrl = [
        'debug' => 'dist/require',
        'prod' => 'dist/require.min'
    ];

    /**
     * Inserts <script> tag for including require.js
     *
     * ### Options
     *
     * - `jsUrl` Base url to javascript. 'app/webroot/js' by default.
     * - `requireUrl Url to require.js. Without '.js' extension.
     *
     * @param string $dataMain data-main tag start script without .js extension
     * @param array $options additional options
     * @return string
     */
    public function scriptTag($dataMain, $options = [])
    {
        // require.js should already be included in production js
        $options += [
            'jsUrl' => $this->_jsRoot(),
            'requireUrl' => $this->_requireUrl()
        ];
        $out = '';
        // add version as timestamp to require requests
        $isTimestampShown = Configure::read('Asset.timestamp');
        if ($isTimestampShown === 'force'
            || ($isTimestampShown === true && Configure::read('debug') > 0)
        ) {
            $out .= $this->Html->scriptBlock(
                "var require = {urlArgs:" .
                $this->Js->value(
                    $this->Html->getAssetTimestamp(
                        $options['jsUrl'] . $dataMain . '.js'
                    )
                ) . " }"
            );
        }
        // require.js borks out when used with Cakes timestamp.
        // also we need the relative path for the main-script
        $_tmpAssetTimestampCache = Configure::read('Asset.timestamp');
        Configure::write('Asset.timestamp', false);
        $out .= $this->Html->script(
            $this->Url->assetUrl(
                $options['requireUrl'],
                ['ext' => '.js', 'fullBase' => true]
            ),
            [
                'data-main' => $this->Url->assetUrl(
                    $dataMain,
                    [
                        'pathPrefix' => $options['jsUrl'],
                        'ext' => '.js'
                    ]
                )
            ]
        );
        Configure::write('Asset.timestamp', $_tmpAssetTimestampCache);

        return $out;
    }

    /**
     * js root
     *
     * @return mixed|string
     */
    protected function _jsRoot()
    {
        $debug = Configure::read('debug') > 0;
        if ($debug) {
            return Configure::read('App.jsBaseUrl');
        } else {
            return Configure::read('App.jsBaseUrl') . '/../dist/';
        }
    }

    /**
     * require url
     *
     * @return mixed
     */
    protected function _requireUrl()
    {
        $debug = Configure::read('debug') > 0;
        if ($debug) {
            return $this->_requireUrl['debug'];
        } else {
            return $this->_requireUrl['prod'];
        }
    }
}
