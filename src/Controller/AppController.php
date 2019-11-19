<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use App\Controller\Component\AuthUserComponent;
use App\Controller\Component\JsDataComponent;
use App\Controller\Component\RefererComponent;
use App\Controller\Component\SaitoEmailComponent;
use App\Controller\Component\SlidetabsComponent;
use App\Controller\Component\ThemesComponent;
use App\Controller\Component\TitleComponent;
use App\Model\Table\UsersTable;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\I18n\I18n;
use Saito\App\SettingsImmutable;
use Saito\Event\SaitoEventManager;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Class AppController
 *
 * @property AuthUserComponent $AuthUser
 * @property AuthenticationComponent $Authentication
 * @property JsDataComponent $JsData
 * @property RefererComponent $Referer
 * @property SaitoEmailComponent $SaitoEmail
 * @property SlidetabsComponent $Slidetabs
 * @property ThemesComponent $Themes
 * @property TitleComponent $Title
 * @property UsersTable $Users
 */
class AppController extends Controller
{
    use InstanceConfigTrait;

    public $helpers = [
        'Form' => [
            // Bootstrap 4 CSS-class for invalid input elements
            'errorClass' => 'is-invalid',
            'templates' => [
                // Bootstrap 4 CSS-class for input validation message
                'error' => '<div class="invalid-feedback">{{content}}</div>',
            ],
        ],
        'Html',
        'JsData',
        'Layout',
        'Permissions',
        'SaitoHelp.SaitoHelp',
        'Stopwatch.Stopwatch',
        'TimeH',
        'Url',
        'User',
    ];

    /**
     * Default config used by InstanceConfigTrait
     *
     * @var array default configuration
     */
    protected $_defaultConfig = [
        'showStopwatch' => false
    ];

    /**
     * The current user, set by the AuthUserComponent
     *
     * @var CurrentUserInterface
     */
    public $CurrentUser;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        Stopwatch::start('------------------- Controller -------------------');

        parent::initialize();

        $this->setConfig('showStopwatch', Configure::read('debug'));

        if (!$this->request->is('requested')) {
            $this->request->getSession()->start();
        }

        // Leave in front to have it available in all Components
        $this->loadComponent('Detectors.Detectors');
        $this->loadComponent('Cookie');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Security', ['blackHoleCallback' => 'blackhole']);
        $this->loadComponent('Csrf', ['expiry' => time() + 10800]);
        if (PHP_SAPI !== 'cli') {
            // if: The security mock in testing doesn't allow seeting cookie-name.
            $this->Csrf->setConfig('cookieName', Configure::read('Session.cookie') . '-CSRF');
        }
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
        $this->loadComponent('Cron.Cron');
        $this->loadComponent('CacheSupport');
        $this->loadComponent('AuthUser');
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

        // disable forum with admin pref
        if (Configure::read('Saito.Settings.forum_disabled') &&
            $this->request->getParam('action') !== 'login' &&
            !$this->CurrentUser->permission('saito.core.admin.backend')
        ) {
            $this->Themes->setDefault();
            $this->viewBuilder()->enableAutoLayout(false);
            $this->render('/Pages/forum_disabled');
            $this->response = $this->response->withStatus(503);

            return null;
        }

        // allow sql explain for DebugKit toolbar
        if ($this->request->getParam('plugin') === 'debug_kit') {
            $this->Authentication->allowUnauthenticated(['sql_explain']);
        }

        Stopwatch::stop('App->beforeFilter()');
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        Stopwatch::start('App->beforeRender()');
        $this->Themes->set($this->CurrentUser);
        $this->_setConfigurationFromGetParams();
        $this->_l10nRenderFile();

        $this->set('SaitoSettings', new SettingsImmutable(Configure::read('Saito.Settings')));
        $this->set('SaitoEventManager', SaitoEventManager::getInstance());
        $this->set('showStopwatch', $this->getConfig('showStopwatch'));

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
            $this->Themes->set($this->CurrentUser, $theme);
        }

        //= activate stopwatch
        $stopwatch = $this->request->getQuery('stopwatch');
        if ($stopwatch && Configure::read('Saito.Settings.stopwatch_get')
        ) {
            $this->setConfig('showStopwatch', true);
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
        $this->Flash->set(__('authorization.autherror'), ['element' => 'error']);
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
        I18n::useFallback(false); // @see <https://github.com/cakephp/cakephp/pull/6812>
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
}
