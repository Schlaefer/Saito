<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;

use Cake\I18n\I18n;
use Cake\Network\Exception\ForbiddenException;
use Cake\Routing\Router;
use Cake\Utility\String;
use Saito\App\Settings;
use Saito\Event\SaitoEventManager;
use Saito\String\Properize;
use Saito\User\Permission;
use Saito\User\SaitoUser;
use \Stopwatch\Lib\Stopwatch;
use Saito\App\Registry;
use Saito\Exception\SaitoBlackholeException;

class AppController extends Controller
{

    public $components = [
//			 'DebugKit.Toolbar',

        // Leave in front to catch all unauthorized access first
        'Security',
        // 'Auth',
        // Leave in front to have it available in all Components
        //'Detectors.Detectors',
        /**
         * You have to have Cookie before CurrentUser to have the salt initialized.
         * Check by deleting Session cookie when persistent cookie is present.
         *
         * @td maybe bug in Cake, because Cookies should be initialized in CurrentUser's $components
         */
        'Cookie',
        // CurrentUser
        // 'CacheSupport',
//			'Parser',
//			'SaitoEmail',
//			'EmailNotification',
        // Enabling data view for rss/xml and json
//			'RequestHandler',
//			'Session',
//			'Themes'
    ];

    public $helpers = [
        'JsData',
        'Markitup.Markitup',
        'Layout',
        'RequireJs',
        'SaitoHelp.SaitoHelp',
        'Stopwatch.Stopwatch',
        'TimeH',
        'UserH',
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
     * S(l)idetabs used by the application
     *
     * @var array
     */
    public $installedSlidetabs = [
        /*
         * @todo 3.0
        'slidetab_recentposts',
        'slidetab_recententries',
        */
        'slidetab_userlist',
        'slidetab_shoutbox'
    ];

    /**
     * @var bool show disclaimer in page footer
     */
    public $showDisclaimer = false;

    public function initialize()
    {
        Stopwatch::start(
            '---------------------- Controller ----------------------'
        );
        Registry::initialize();

        if (!$this->request->is('requested')) {
            $this->request->session()->start();
        }
        if (php_sapi_name() === 'cli') {
            $this->request->session()->id('test');
        }

        // Leave in front to have it available in all Components
        $this->loadComponent('Detectors.Detectors');
        $this->loadComponent('Auth');
        $this->loadComponent('ActionAuthorization');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Cron.Cron');
        $this->loadComponent('CacheSupport');
        $this->loadComponent('CurrentUser');
        $this->loadComponent('Shouts');
        $this->loadComponent('JsData');
        $this->loadComponent('Parser');
        $this->loadComponent('SaitoEmail');
        $this->loadComponent('EmailNotification');
        $this->loadComponent('Themes');
        $this->loadComponent('Flash');
        $this->loadComponent('Title');
    }

    public function beforeFilter(Event $event)
    {
        Stopwatch::start('App->beforeFilter()');

        // must be called before CakeError early return
        $this->Themes->theme(Configure::read('Saito.themes'));
        $this->loadModel('Settings');
        $this->Settings->load(Configure::read('Saito.Settings'));

        // CakeErrors run through this beforeFilter, which is usually not necessary
        // for error messages
        // @todo 3.0
        if ($this->name === 'CakeError') {
            return;
        }

        $this->Security->blackHoleCallback = 'blackhole';
        $this->Security->csrfUseOnce = false;
        $this->Security->csrfExpires = '+3 hours';

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

            return $this->render('/Pages/forum_disabled', 'barebone');
            exit;
        }

        $this->_setupSlideTabs();

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

    public function beforeRender(Event $event)
    {
        Stopwatch::start('App->beforeRender()');

        if ($this->showDisclaimer) {
            $this->_showDisclaimer();
        }

        $this->set(
            'SaitoSettings',
            new Settings(Configure::read('Saito.Settings'))
        );
        $this->set('SaitoEventManager', SaitoEventManager::getInstance());

        $this->set('lastAction', $this->localReferer('action'));
        $this->set('lastController', $this->localReferer('controller'));
        $this->set('isDebug', (int)Configure::read('debug') > 0);
        $this->_setLayoutTitles();

        $this->_setXFrameOptionsHeader();

        Stopwatch::stop('App->beforeRender()');
        Stopwatch::start(
            '---------------------- Rendering ---------------------- '
        );
    }

    /**
     * Sets forum configuration from GET parameter in url
     *
     * - theme=<foo>
     * - stopwatch:true
     * - lang:<lang_id>
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
        if (isset($this->request->query['stopwatch']) && Configure::read(
                'Saito.Settings.stopwatch_get'
            )
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
     * sets layout/title/page vars
     *
     * @td helper?
     */
    protected function _setLayoutTitles()
    {
        $_pageTitle = $this->_setPageTitle();
        $_forumName = $this->_setForumName();
        $this->_setForumTitle($_pageTitle, $_forumName);
    }

    /**
     * Sets forum name according to forum settings if not already set
     *
     * @return string
     */
    protected function _setForumName()
    {
        if (isset($this->viewVars['forum_name'])) {
            return $this->viewVars['forum_name'];
        }
        $_forumName = Configure::read('Saito.Settings.forum_name');
        $this->set('forum_name', $_forumName);

        return $_forumName;
    }

    /**
     * Sets forum title `<page> - <forum>`
     *
     * @param string $pageTitle
     * @param string $forumName
     * @return string
     */
    protected function _setForumTitle($pageTitle, $forumName)
    {
        $_forumTitle = $pageTitle;
        if (!empty($forumName)) {
            $_forumTitle = String::insert(
                __('forum-title-template'),
                ['page' => $pageTitle, 'forum' => $forumName]
            );
        }
        $this->set('title_for_layout', $_forumTitle);

        return $_forumTitle;
    }

    /**
     * Sets page title
     *
     * Looks in this order for:
     * 1. title_for_page
     * 2. title_for_layout
     * 3. `page_titles.po` language file with 'controller/view' title,
     *        use plural for for controller title: 'entries/index' (not
     * 'entry/index')!
     *
     * @return string
     */
    protected function _setPageTitle()
    {
        if (isset($this->viewVars['title_for_page'])) {
            $_pageTitle = $this->viewVars['title_for_page'];
        } elseif (isset($this->viewVars['title_for_layout'])) {
            // provides CakePHP backwards-compatibility
            $_pageTitle = $this->viewVars['title_for_layout'];
        } elseif ($this->name === 'CakeError') {
            $_pageTitle = '';
        } else {
            $_pageTitle = __d(
                'page_titles',
                $this->params['controller'] . '/' . $this->params['action']
            );
        }
        $this->set('title_for_page', $_pageTitle);

        return $_pageTitle;
    }

    protected function _setXFrameOptionsHeader()
    {
        $xFO = Configure::read('Saito.X-Frame-Options');
        if (empty($xFO)) {
            $xFO = 'SAMEORIGIN';
        }
        $this->response->header('X-Frame-Options', $xFO);
    }

    /**
     *
     *
     * @param $type
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
     * Setup which slidetabs are available and user sorting
     *
     * @throws ForbiddenException
     */
    protected function _setupSlideTabs()
    {
        $slidetabs = $this->installedSlidetabs;

        if (!empty($this->CurrentUser['slidetab_order'])) {
            $slidetabsUser = unserialize($this->CurrentUser['slidetab_order']);
            // disabled tabs still set in user-prefs are unset
            $slidetabsUser = array_intersect(
                $slidetabsUser,
                $this->installedSlidetabs
            );
            // new tabs not set in user-prefs are added
            $slidetabs = array_unique(
                array_merge($slidetabsUser, $this->installedSlidetabs)
            );
        }
        if (Configure::read('Saito.Settings.shoutbox_enabled') == false) {
            unset($slidetabs[array_search('slidetab_shoutbox', $slidetabs)]);
        }
        $this->set('slidetabs', $slidetabs);
    }

    /**
     * manually require auth and redirect cycle
     */
    protected function _requireAuth()
    {
        $this->Flash->set(__('auth_autherror'), ['element' => 'warning']);
        $here = $this->request->here(false);
        $this->Auth->redirectUrl($here);
        $this->redirect(['controller' => 'users', 'action' => 'login']);
    }

    /**
     * Shows the disclaimer in the layout
     */
    protected function _showDisclaimer()
    {
        $this->set('showDisclaimer', true);
    }

    /**
     * show Slidetabs in layout
     */
    protected function _showSlidetabs()
    {
        if ($this->CurrentUser->isLoggedIn()) {
            $this->set('showSlidetabs', true);
        }
    }

    /**
     * sets l10n .ctp file if available
     */
    protected function _l10nRenderFile()
    {
        $locale = Configure::read('Saito.language');
        I18n::locale($locale);
        $l10nViewPath = $this->viewPath . DS . $locale;
        $l10nViewFile = $l10nViewPath . DS . $this->view . '.ctp';
        if ($locale && file_exists(APP . 'Template' . DS . $l10nViewFile)
        ) {
            $this->viewPath = $l10nViewPath;
        }
    }

    /**
     * @param $user
     * @return bool
     */
    public function isAuthorized($user)
    {
        $user = new SaitoUser($user);
        $action = $this->request->action;

        return $this->ActionAuthorization->isAuthorized($user, $action);
    }

}
