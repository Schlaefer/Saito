<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use App\Controller\Component\CurrentUserComponent;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\View\Helper;
use Cake\View\View;
use Saito\JsData\JsData;
use Saito\User\ForumsUserInterface;

/**
 * Javascript Data Helper
 */
class JsDataHelper extends AppHelper
{

    public $helpers = ['Url'];

    /**
     * JsData
     *
     * @var JsData
     */
    protected $_JsData;

    /**
     * CakePHP beforeRender event-handler
     *
     * @param Event $event event
     * @param mixed $viewFile view file
     * @return void
     */
    public function beforeRender(Event $event, $viewFile): void
    {
        $View = $event->getSubject();
        $this->_JsData = $View->get('jsData');
    }

    /**
     * get app js
     *
     * @param View $View view
     * @param ForumsUserInterface $CurrentUser user
     * @return string
     */
    public function getAppJs(View $View, ForumsUserInterface $CurrentUser)
    {
        $js = $this->_JsData->getJs();
        $js += [
            'app' => [
                'version' => Configure::read('Saito.v'),
                'settings' => [
                    'autoPageReload' => (isset($View->viewVars['autoPageReload']) ? $View->viewVars['autoPageReload'] : 0),
                    'embedly_enabled' => (bool)Configure::read(
                        'Saito.Settings.embedly_enabled'
                    ),
                    'editPeriod' => (int)Configure::read(
                        'Saito.Settings.edit_period'
                    ),
                    'notificationIcon' => $this->Url->assetUrl(
                        'html5-notification-icon.png',
                        [
                            'pathPrefix' => Configure::read('App.imageBaseUrl'),
                            'fullBase' => true
                        ]
                    ),
                    'subject_maxlength' => (int)Configure::read('Saito.Settings.subject_maxlength'),
                    'upload_max_img_size' => (int)Configure::read('Saito.Settings.upload_max_img_size') * 1024,
                    'upload_max_number_of_uploads' => (int)Configure::read('Saito.Settings.upload_max_number_of_uploads'),
                    'theme' => $View->getTheme(),
                    'apiroot' => $View->request->getAttribute('webroot') . 'api/v2/',
                    'webroot' => $View->request->getAttribute('webroot')
                ]
            ],
            'request' => [
                'action' => $View->request->getParam('action'),
                'controller' => $View->request->getParam('controller'),
                'isMobile' => $View->request->isMobile(),
                'isPreview' => $View->request->isPreview(),
                'csrf' => $this->_getCsrf($View)
            ],
            'currentUser' => [
                'id' => (int)$CurrentUser->get('id'),
                'username' => $CurrentUser->get('username'),
                'user_show_inline' => $CurrentUser->get('inline_view_on_click') || false,
                'user_show_thread_collapsed' => $CurrentUser->get('user_show_thread_collapsed') || false
            ],
            'callbacks' => [
                'beforeAppInit' => [],
                'afterAppInit' => [],
                'afterViewInit' => []
            ]
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
        $key = 'csrfToken';
        $token = $View->request->getCookie($key);
        if (empty($token)) {
            $token = $View->response->getCookie($key)['value'];
        }
        $header = 'X-CSRF-Token';

        return compact('header', 'token');
    }

    /**
     * Passes method calls on to JsData
     *
     * {@inheritDoc}
     */
    public function __call($method, $params)
    {
        $proxy = [$this->_JsData, $method];
        if (is_callable($proxy)) {
            return call_user_func_array($proxy, $params);
        }

        parent::__call($method, $params);
    }
}
