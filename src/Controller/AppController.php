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

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\EventInterface;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Closure;
use Saito\App\Registry;
use Saito\App\SettingsImmutable;
use Saito\Event\SaitoEventManager;
use Stopwatch\Lib\Stopwatch;

/**
 * Class AppController
 *
 * @property \App\Controller\Component\AuthUserComponent $AuthUser
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \App\Controller\Component\JsDataComponent $JsData
 * @property \App\Controller\Component\RefererComponent $Referer
 * @property \App\Controller\Component\SaitoEmailComponent $SaitoEmail
 * @property \App\Controller\Component\SlidetabsComponent $Slidetabs
 * @property \App\Controller\Component\ThemesComponent $Themes
 * @property \App\Controller\Component\TitleComponent $Title
 * @property \App\Model\Table\UsersTable $Users
 * @property \Cake\Controller\Component\FormProtectionComponent $FormProtection
 */
class AppController extends Controller
{
    use InstanceConfigTrait;

    /**
     * Default config used by InstanceConfigTrait
     *
     * @var array default configuration
     */
    protected $_defaultConfig = [
        'showStopwatch' => false,
    ];

    /**
     * The current user, set by the AuthUserComponent
     *
     * @var \Saito\User\CurrentUser\CurrentUserInterface
     */
    public $CurrentUser;

    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        Stopwatch::start('------------------- Controller -------------------');

        parent::initialize();

        $this->setConfig('showStopwatch', Configure::read('debug'));

        if (!$this->request->is('requested')) {
            $this->request->getSession()->start();
        }

        Registry::get('Permissions')->buildCategories(TableRegistry::getTableLocator()->get('Categories'));

        // Leave in front to have it available in all Components
        $this->loadComponent('Detectors.Detectors');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('FormProtection', [
            'validationFailureCallback' => Closure::fromCallable([$this, 'blackhole']),
        ]);
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
    public function beforeFilter(EventInterface $event)
    {
        Stopwatch::start('App->beforeFilter()');

        // disable forum with admin pref
        if (
            Configure::read('Saito.Settings.forum_disabled') &&
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

        $this->viewBuilder()->setHelpers(['Form' => [
            // Bootstrap 4 CSS-class for invalid input elements
            'errorClass' => 'is-invalid',
            'templates' => [
                // Bootstrap 4 CSS-class for input validation message
                'error' => '<div class="invalid-feedback">{{content}}</div>',
            ],
        ]]);

        $this->Themes->set($this->CurrentUser);
        $this->_setConfigurationFromGetParams();
        $this->_l10nRenderFile();

        Stopwatch::stop('App->beforeFilter()');
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        Stopwatch::start('App->beforeRender()');

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
        if (
            $stopwatch && Configure::read('Saito.Settings.stopwatch_get')
        ) {
            $this->setConfig('showStopwatch', true);
        }

        //= change language
        $lang = $this->request->getQuery('lang');
        if (!empty($lang)) {
            Configure::write('Saito.language', $lang);
        }
    }

    /**
     * Handle request-blackhole.
     *
     * @param \Exception $exception PHP exception
     * @return void
     * @throws \Saito\Exception\SaitoBlackholeException
     */
    public function blackhole(\Exception $exception): void
    {
        throw new \Saito\Exception\SaitoBlackholeException(
            $exception->getMessage(),
            ['CurrentUser' => $this->CurrentUser]
        );
    }

    /**
     * manually require auth and redirect cycle
     *
     * @return \Cake\Http\Response
     */
    protected function _requireAuth()
    {
        $this->Flash->set(__('authorization.autherror'), ['element' => 'error']);
        $here = $this->request->getRequestTarget();

        return $this->redirect([
            '_name' => 'login',
            '?' => ['redirect' => $here],
            'plugin' => false,
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
            $this->viewBuilder()->setTemplatePath($path);

            return;
        }

        if (strpos($locale, '_')) {
            [$locale] = explode('_', $locale);
            $path = $check($locale);
            if ($path) {
                $this->viewBuilder()->setTemplatePath($path);
            }
        }
    }
}
