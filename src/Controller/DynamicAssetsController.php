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
                    $msgs += $Loader()->getMessages();
                }

                return json_encode($msgs);
            }
        );

        $this->response->type('json');
        $this->response->cache('-1 minute', '+1 hour');
        $this->response->compress();
        $this->response->body($msgs);

        return $this->response;
    }

}
