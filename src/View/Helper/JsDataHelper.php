<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use Cake\View\Helper\UrlHelper;
use Cake\View\View;
use Saito\JsData\Notifications;
use Saito\User\ForumsUserInterface;

/**
 * Javascript Data Helper
 *
 * @property ServerRequest $request
 * @property UrlHelper $Url
 */
class JsDataHelper extends AppHelper
{
    public $helpers = ['Url'];

    /**
     * Notifications
     *
     * @var Notifications
     */
    protected $Notifications;

    /**
     * get app js
     *
     * @param View $View view
     * @param ForumsUserInterface $CurrentUser user
     * @return string
     */
    public function getAppJs(View $View, ForumsUserInterface $CurrentUser)
    {
        $request = $View->getRequest();

        $js = [
            'app' => [
                'settings' => [
                    'autoPageReload' => (isset($View->viewVars['autoPageReload']) ? $View->viewVars['autoPageReload'] : 0),
                    'editPeriod' => (int)Configure::read(
                        'Saito.Settings.edit_period'
                    ),
                    'language' => Configure::read('Saito.language'),
                    'notificationIcon' => $this->Url->assetUrl(
                        'html5-notification-icon.png',
                        [
                            'pathPrefix' => Configure::read('App.imageBaseUrl'),
                            'fullBase' => true,
                        ]
                    ),
                    'theme' => $View->getTheme(),
                    'apiroot' => $request->getAttribute('webroot') . 'api/v2/',
                    'webroot' => $request->getAttribute('webroot'),
                ],
            ],
            'assets' => [
                'lang' => $this->Url->assetUrl('js/locale/' . Configure::read('Saito.language') . '.json'),
            ],
            'msg' => $this->notifications()->getAll(),
            'request' => [
                'action' => $request->getParam('action'),
                'controller' => mb_strtolower($request->getParam('controller')),
                'isMobile' => $request->is('mobile'),
                'csrf' => $this->_getCsrf($View),
            ],
            'currentUser' => [
                'id' => (int)$CurrentUser->get('id'),
                'username' => $CurrentUser->get('username'),
                'user_show_inline' => $CurrentUser->get('inline_view_on_click') ?: false,
                'user_show_thread_collapsed' => $CurrentUser->get('user_show_thread_collapsed') ?: false,
            ],
            'callbacks' => [
                'beforeAppInit' => [],
                'afterAppInit' => [],
                'afterViewInit' => [],
            ],
        ];
        $out = 'var SaitoApp = ' . json_encode($js);
        $out .= '; SaitoApp.timeAppStart = new Date().getTime();';

        return $out;
    }

    /**
     * Get CSRF-config
     *
     * @param View $View View
     * @return array
     * - 'header' HTTP header for CSRF-token
     * - 'token' CSRF-token
     */
    protected function _getCsrf(View $View)
    {
        $key = Configure::read('Session.cookie') . '-CSRF';
        $token = $View->getRequest()->getCookie($key);
        if ($token === null) {
            // First request without CSRF cookie set yet. CSRF set as new cookie
            // in this request.
            $token = $View->getResponse()->getCookie($key)['value'];
        }
        $header = 'X-CSRF-Token';

        return compact('header', 'token');
    }

    /**
     * Gets notifications
     *
     * @return Notifications The notifications.
     */
    public function notifications(): Notifications
    {
        if (empty($this->Notifications)) {
            $this->Notifications = new Notifications();
        }

        return $this->Notifications;
    }
}
