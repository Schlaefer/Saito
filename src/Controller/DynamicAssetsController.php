<?php

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\I18n\MessagesFileLoader;

/**
 * Class DynamicAssets
 *
 * Serve dynamic assets bypassing app logic
 *
 * If performance becomes an issue consider writing out as static assets
 * into webroot
 */
class DynamicAssetsController extends Controller
{

    public $components = [];

    public $autoRender = false;

    /**
     * Output current language strings as json
     *
     * @return string
     */
    public function langJs()
    {
        $lang = Configure::read('Saito.language');
        $msgs = Cache::remember(
            'Saito.langJs.' . $lang,
            function () use ($lang) {
                $msgs = [];
                $names = ['default', 'nondynamic'];
                foreach ($names as $name) {
                    $Loader = new MessagesFileLoader($name, $lang, 'po');
                    $messages = $Loader()->getMessages();
                    /**
                     * usual message format CakePHP 3
                     *
                     * [
                     *      '<string>' => [
                     *          '_context' => [
                     *              '' => '<translation>'
                     *          ]
                     *      ],
                     *      …
                     *]
                     */
                    foreach ($messages as $string => $translation) {
                        if (empty($translation['_context'][''])) {
                            continue;
                        }
                        $msgs[$string] = $translation['_context'][''];
                    }
                }

                return json_encode($msgs);
            }
        );

        $this->response = $this->response
            ->withType('json')
            ->withCache('-1 minute', '+1 hour')
            ->withStringBody($msgs);
        // compress does not return response object
        $this->response->compress();

        return $this->response;
    }
}
