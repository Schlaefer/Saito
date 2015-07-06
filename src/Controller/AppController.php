<?php

namespace App\Controller;

use App\Controller\Component\CurrentUserComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\I18n;
use Cake\Network\Http\Response;
use Cake\Routing\Router;
use Saito\App\Registry;
use Saito\App\Settings;
use Saito\Event\SaitoEventManager;
use Saito\String\Properize;
use Saito\User\CurrentUser\CurrentUser;
use \Stopwatch\Lib\Stopwatch;

/**
 * Class AppController
 *
 * @property CurrentUserComponent $CurrentUser
 * @package App\Controller
 */
class AppController extends Controller
{
    public $helpers = [
        'JsData',
        'Markitup.Markitup',
        'Layout',
        'RequireJs',
        'SaitoHelp.SaitoHelp',
        'Stopwatch.Stopwatch',
        'TimeH',
        'User',
        'Form',
        'Html',
        'Url'
    ];

    /**
     * name of the theme used
     *
     * @var string
     */
    public $theme = 'Paz';

    /**
     * @var bool show disclaimer in page footer
     */
    public $showDisclaimer = false;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        Stopwatch::start('------------------- Controller -------------------');
        Registry::initialize();

        if (!$this->request->is('requested')) {
            $this->request->session()->start();
        }
        if (php_sapi_name() === 'cli') {
            $this->request->session()->id('test');
        }

        // Leave in front to have it available in all Components
        $this->loadComponent('Detectors.Detectors');
        $this->loadComponent('Cookie');
        $this->loadComponent('Auth');
        $this->loadComponent('ActionAuthorization');
        $this->loadComponent('Security', ['blackHoleCallback' => 'blackhole']);
        $this->loadComponent('Csrf', ['expiry' => time() + 10800]);
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Cron.Cron');
        $this->loadComponent('CacheSupport');
        $this->loadComponent('CurrentUser');
        $this->loadComponent('JsData');
        $this->loadComponent('Parser');
        $this->loadComponent('SaitoEmail');
        $this->loadComponent('Slidetabs');
        // @td 3.0 Notif
        //$this->loadComponent('EmailNotification');
        $this->loadComponent('Themes');
        $this->loadComponent('Flash');
        $this->loadComponent('Title');
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        Stopwatch::start('App->beforeFilter()');

        // must be called before CakeError early return
        $this->Themes->theme(Configure::read('Saito.themes'), $this->CurrentUser);
        $this->loadModel('Settings');
        $this->Settings->load(Configure::read('Saito.Settings'));

        // activate stopwatch in debug mode
        $this->set('showStopwatchOutput', false);
        if ((int)Configure::read('debug') > 0) {
            $this->set('showStopwatchOutput', true);
        };

        // setup for admin area
        if ($this->request->param('prefix') === 'admin') {
            $this->layout = 'admin';
        }

        // disable forum with admin pref
        if (Configure::read('Saito.Settings.forum_disabled') &&
            $this->request['action'] !== 'login' &&
            !$this->CurrentUser->permission('saito.core.admin.backend')
        ) {
            $this->Themes->setDefault();
            $this->render('/Pages/forum_disabled', 'barebone');
            exit;
        }

        $this->_setConfigurationFromGetParams();

        // must be set after all language chooser
        Properize::setLanguage(Configure::read('Saito.language'));

        // allow sql explain for DebugKit toolbar
        if ($this->request->plugin === 'debug_kit') {
            $this->Auth->allow('sql_explain');
        }

        $this->_l10nRenderFile();

        Stopwatch::stop('App->beforeFilter()');
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        Stopwatch::start('App->beforeRender()');
        $this->set(
            'SaitoSettings',
            new Settings(Configure::read('Saito.Settings'))
        );
        $this->set('SaitoEventManager', SaitoEventManager::getInstance());

        $this->set('showDisclaimer', $this->showDisclaimer);
        $this->set('lastAction', $this->localReferer('action'));
        $this->set('lastController', $this->localReferer('controller'));
        $this->set('isDebug', (int)Configure::read('debug') > 0);

        Stopwatch::stop('App->beforeRender()');
        Stopwatch::start('------------------- Rendering --------------------');
    }

    /**
     * Sets forum configuration from GET parameter in url
     *
     * - theme=<foo>
     * - stopwatch:true
     * - lang:<lang_id>
     *
     * @return void
     */
    protected function _setConfigurationFromGetParams()
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            return;
        }

        //= change theme on the fly with ?theme=<name>
        if (isset($this->request->query['theme'])) {
            $this->theme = $this->request->query['theme'];
        }

        //= activate stopwatch
        if (isset($this->request->query['stopwatch'])
            && Configure::read('Saito.Settings.stopwatch_get')
        ) {
            $this->set('showStopwatchOutput', true);
        };

        //= change language
        $lang = $this->request->query('lang');
        if (!empty($lang)) {
            Configure::write('Saito.language', $lang);
        };
    }

    /**
     * Handle request-blackhole.
     *
     * @param string $type type
     * @return void
     * @throws \Saito\Exception\SaitoBlackholeException
     */
    public function blackhole($type)
    {
        throw new \Saito\Exception\SaitoBlackholeException(
            $type,
            ['CurrentUser' => $this->CurrentUser]
        );
    }

    /**
     * Custom referer which can return only referer's action or controller
     *
     * @param string $type 'controller' or 'action'
     * @return string
     */
    public function localReferer($type = null)
    {
        $referer = parent::referer(null, true);
        $parsed = Router::parse($referer);
        if (isset($parsed[$type])) {
            return $parsed[$type];
        } else {
            if ($type === 'action') {
                return 'index';
            } elseif ($type === 'controller') {
                return 'entries';
            }
        }
        return $referer;
    }

    /**
     * manually require auth and redirect cycle
     *
     * @return Response
     */
    protected function _requireAuth()
    {
        $this->Flash->set(__('auth_autherror'), ['element' => 'warning']);
        $here = $this->request->here(false);
        $this->Auth->redirectUrl($here);
        return $this->redirect(['controller' => 'Users', 'action' => 'login', 'plugin' => false]);
    }

    /**
     * sets l10n .ctp file if available
     *
     * @return void
     */
    protected function _l10nRenderFile()
    {
        $locale = Configure::read('Saito.language');
        I18n::locale($locale);
        if (!$locale) {
            return;
        }

        $check = function ($locale) {
            $l10nViewPath = $this->viewPath . DS . $locale;
            $l10nViewFile = $l10nViewPath . DS . $this->view . '.ctp';
            if (!file_exists(APP . 'Template' . DS . $l10nViewFile)) {
                return false;
            }
            return $l10nViewPath;
        };

        $path = $check($locale);
        if ($path) {
            $this->viewPath = $path;
            return;
        }

        if (strpos($locale, '_')) {
            list($locale) = explode('_', $locale);
            $path = $check($locale);
            if ($path) {
                $this->viewPath = $path;
            }
        }
    }

    /**
     * Check if user is authorized.
     *
     * @param array $user Session.Auth
     * @return bool
     */
    public function isAuthorized(array $user)
    {
        $user = new CurrentUser($user);
        $action = $this->request->action;

        return $this->ActionAuthorization->isAuthorized($user, $action);
    }
}
