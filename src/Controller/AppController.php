<?php

namespace App\Controller;

use App\Controller\Component\CurrentUserComponent;
use App\Model\Table\SettingsTable;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\I18n;
use Cake\Network\Http\Response;
use Cake\Routing\Router;
use Saito\App\Registry;
use Saito\App\SettingsImmutable;
use Saito\Event\SaitoEventManager;
use Saito\User\CurrentUser\CurrentUser;
use Stopwatch\Lib\Stopwatch;

/**
 * Class AppController
 *
 * @property ActionAuthorizationComponent $ActionAuthorization
 * @property CurrentUserComponent $CurrentUser
 * @property JsDataComponent $JsData
 * @property SaitoEmailComponent $SaitoEmail
 * @property SlidetabsComponent $Slidetabs
 * @property SettingsTable $Settings
 * @property ThemesComponent $Themes
 * @property TitleComponent $Title
 * @package App\Controller
 */
class AppController extends Controller
{
    public $helpers = [
        'Form',
        'Html',
        'JsData',
        'Layout',
        'Markitup.Markitup',
        'RequireJs',
        'SaitoHelp.SaitoHelp',
        'Stopwatch.Stopwatch',
        'TimeH',
        'Url',
        'User',
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        Stopwatch::start('------------------- Controller -------------------');
        Registry::initialize();

        parent::initialize();

        if (!$this->request->is('requested')) {
            $this->request->getSession()->start();
        }

        // Leave in front to have it available in all Components
        $this->loadComponent('Detectors.Detectors');
        $this->loadComponent('Cookie');
        $this->loadComponent('Auth');
        $this->loadComponent('ActionAuthorization');
        $this->loadComponent('Security', ['blackHoleCallback' => 'blackhole']);
        $this->loadComponent('Csrf', ['expiry' => time() + 10800]);
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
        $this->loadComponent('Cron.Cron');
        $this->loadComponent('CacheSupport');
        $this->loadComponent('CurrentUser');
        $this->loadComponent('JsData');
        $this->loadComponent('Parser');
        $this->loadComponent('SaitoEmail');
        $this->loadComponent('Slidetabs');
        $this->loadComponent('Themes', Configure::read('Saito.themes'));
        $this->loadComponent('Flash');
        $this->loadComponent('Title');
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        Stopwatch::start('App->beforeFilter()');

        $this->Themes->set();
        $this->loadModel('Settings');
        $this->Settings->load(Configure::read('Saito.Settings'));

        // activate stopwatch in debug mode
        $this->set('showStopwatchOutput', false);
        if ((int)Configure::read('debug') > 0) {
            $this->set('showStopwatchOutput', true);
        };

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

        // allow sql explain for DebugKit toolbar
        if ($this->request->getParam('plugin') === 'debug_kit') {
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
        $this->set('SaitoSettings', new SettingsImmutable(Configure::read('Saito.Settings')));
        $this->set('SaitoEventManager', SaitoEventManager::getInstance());

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
        $theme = $this->request->getQuery('theme');
        if ($theme) {
            $this->Themes->set($theme);
        }

        //= activate stopwatch
        $stopwatch = $this->request->getQuery('stopwatch');
        if ($stopwatch && Configure::read('Saito.Settings.stopwatch_get')
        ) {
            $this->set('showStopwatchOutput', true);
        };

        //= change language
        $lang = $this->request->getQuery('lang');
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
     * manually require auth and redirect cycle
     *
     * @return Response
     */
    protected function _requireAuth()
    {
        $this->Flash->set(__('auth_autherror'), ['element' => 'warning']);
        $here = $this->request->getRequestTarget();

        return $this->redirect([
            '_name' => 'login',
            '?' => ['redirect' => $here],
            'plugin' => false
        ]);
    }

    /**
     * sets l10n .ctp file if available
     *
     * @return void
     */
    protected function _l10nRenderFile()
    {
        $locale = Configure::read('Saito.language');
        I18n::setLocale($locale);
        if (!$locale) {
            return;
        }

        $check = function ($locale) {
            $l10nViewPath = $this->viewBuilder()->getTemplatePath() . DS . $locale;
            $l10nViewFile = $l10nViewPath . DS . $this->viewBuilder()->getName() . '.ctp';
            if (!file_exists(APP . 'Template' . DS . $l10nViewFile)) {
                return false;
            }

            return $l10nViewPath;
        };

        $path = $check($locale);
        if ($path) {
            $this->viewBuilder()->templatePath($path);

            return;
        }

        if (strpos($locale, '_')) {
            list($locale) = explode('_', $locale);
            $path = $check($locale);
            if ($path) {
                $this->viewBuilder()->templatePath($path);
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
        $action = $this->request->getParam('action');

        return $this->ActionAuthorization->isAuthorized($user, $action);
    }
}
