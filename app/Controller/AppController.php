<?php

	use Saito\Exception\SaitoBlackholeException;

	App::uses('Controller', 'Controller');
	App::uses('CakeEmail', 'Network/Email');
	App::import('Lib', 'Stopwatch.Stopwatch');

	if (Configure::read('debug') > 0) {
		App::uses('FireCake', 'DebugKit.Lib');
	}

	class AppController extends Controller {

		public $components = [
			// 'DebugKit.Toolbar',

			// Leave in front to catch all unauthorized access first
			'Security', 'Auth',
			// Leave in front to have it available in all Components
			'Detectors.Detectors',
/**
 * You have to have Cookie before CurrentUser to have the salt initialized.
 * Check by deleting Session cookie when persistent cookie is present.
 * @td maybe bug in Cake, because Cookies should be initialized in CurrentUser's $components
 */
			'Cookie',
			'CurrentUser',
			'CacheSupport',
			'Cron.Cron',
			'JsData',
			'Parser',
			'SaitoEmail',
			'EmailNotification',
			// Enabling data view for rss/xml and json
			'RequestHandler',
			'Session',
			'Themes'
		];

		public $helpers = [
			'JsData',
			// 'Markitup.Markitup',
			'Layout',
			'RequireJs',
			'SaitoHelp.SaitoHelp',
			'Stopwatch.Stopwatch',
			'TimeH',
			'UserH',
			// CakePHP helpers
			'Js' => array('Jquery'),
			'Html',
			'Form',
			'Session'
		];

		public $uses = [
			'Setting',
			'User'
		];

/**
 * name of the theme used
 *
 * @var string
 */
		public $theme = 'paz';

/**
 * S(l)idetabs used by the application
 *
 * @var array
 */
		public $installedSlidetabs = [
			'slidetab_userlist',
			'slidetab_recentposts',
			'slidetab_recententries',
			'slidetab_shoutbox'
		];

/**
 * Are app stats calculated
 *
 * @var bool
 */
		protected $_areAppStatsSet = false;

/**
 * @var bool show disclaimer in page footer
 */
		public $showDisclaimer = false;

		/**
		 * objects shared between controllers & models
		 *
		 * Avoids a lot of boilerplate code and shuffling around singletons.
		 *
		 * @var array objects shared between controllers & models
		 */
		public $SharedObjects = [];

		public function __construct($request = null, $response = null) {
			Stopwatch::start(
				'---------------------- Controller ----------------------'
			);

			ClassRegistry::addObject('dic', \Saito\DicSetup::getNewDic());
			parent::__construct($request, $response);
		}

		public function __get($name) {
			switch ($name) {
				case 'dic':
					return ClassRegistry::getObject('dic');
				default:
					return parent::__get($name);
			}
		}

		public function beforeFilter() {
			Stopwatch::start('App->beforeFilter()');

			// must be called before CakeError early return
			$this->Themes->theme(Configure::read('Saito.themes'));
			$this->Setting->load(Configure::read('Saito.Settings'));

			// CakeErrors run through this beforeFilter, which is usually not necessary
			// for error messages
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
			if (isset($this->params['admin'])):
				$this->_beforeFilterAdminArea();
			endif;

			// disable forum with admin pref
			if (Configure::read('Saito.Settings.forum_disabled') &&
					$this->request['action'] !== 'login' &&
					!$this->CurrentUser->isAdmin()
			) {
				$this->Themes->setDefault();
				return $this->render('/Pages/forum_disabled', 'barebone');
				exit;
			}

			$this->_setupSlideTabs();

			$this->_setConfigurationFromGetParams();

			// must be set after all language chooser
			\Saito\String\Properize::setLanguage(Configure::read('Config.language'));

			// allow sql explain for DebugKit toolbar
			if ($this->request->plugin === 'debug_kit') {
				$this->Auth->allow('sql_explain');
			}

			$this->_l10nRenderFile();

			Stopwatch::stop('App->beforeFilter()');
		}

		public function beforeRender() {
			Stopwatch::start('App->beforeRender()');

			parent::beforeRender();

			if ($this->showDisclaimer) {
				$this->_showDisclaimer();
			}

			$this->set('lastAction', $this->localReferer('action'));
			$this->set('lastController', $this->localReferer('controller'));
			$this->set('isDebug', (int)Configure::read('debug') > 0);
			$this->_setLayoutTitles();

			$this->_setXFrameOptionsHeader();

			Stopwatch::stop('App->beforeRender()');
			Stopwatch::start('---------------------- Rendering ---------------------- ');
		}

/**
 * Sets forum configuration from GET parameter in url
 *
 * - theme=<foo>
 * - stopwatch:true
 * - lang:<lang_id>
 */
		protected function _setConfigurationFromGetParams() {
			if (!$this->CurrentUser->isLoggedIn()) {
				return;
			}

			// change theme on the fly with ?theme=<name>
			if (isset($this->request->query['theme'])) {
				$this->theme = $this->request->query['theme'];
			}

			// activate stopwatch
			if (isset($this->request->query['stopwatch']) && Configure::read('Saito.Settings.stopwatch_get')) {
				$this->set('showStopwatchOutput', true);
			};

			// change language
			if (isset($this->request->query['lang'])) {
				$L10n = ClassRegistry::init('L10n');
				$lang = $this->request->query['lang'];
				if ($L10n->catalog($lang)) {
					Configure::write('Config.language', $lang);
				}
			};
		}

/**
 * sets layout/title/page vars
 *
 * @td helper?
 */
		protected function _setLayoutTitles() {
			$_pageTitle = $this->_setPageTitle();
			$_forumName = $this->_setForumName();
			$this->_setForumTitle($_pageTitle, $_forumName);
		}

		/**
		 * Sets forum name according to forum settings if not already set
		 *
		 * @return string
		 */
		protected function _setForumName() {
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
		protected function _setForumTitle($pageTitle, $forumName) {
			$_forumTitle = $pageTitle;
			if (!empty($forumName)) {
				$_forumTitle = CakeText::insert(__('forum-title-template'),
						['page' => $pageTitle, 'forum' => $forumName]);
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
		 * 		use plural for for controller title: 'entries/index' (not 'entry/index')!
		 *
		 * @return string
		 */
		protected function _setPageTitle() {
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

		protected function _setXFrameOptionsHeader() {
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
		 * @throws Saito\Exception\SaitoBlackholeException
		 */
		public function blackhole($type) {
			throw new SaitoBlackholeException($type,
				['CurrentUser' => $this->CurrentUser]);
		}

/**
 * Custom referer which can return only referer's action or controller
 *
 * @param string $type 'controller' or 'action'
 * @return string
 */
		public function localReferer($type = null) {
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
		protected function _setupSlideTabs() {
			$slidetabs = $this->installedSlidetabs;

			if (!empty($this->CurrentUser['slidetab_order'])) {
				$slidetabsUser = unserialize($this->CurrentUser['slidetab_order']);
				// disabled tabs still set in user-prefs are unset
				$slidetabsUser = array_intersect($slidetabsUser, $this->installedSlidetabs);
				// new tabs not set in user-prefs are added
				$slidetabs = array_unique(array_merge($slidetabsUser, $this->installedSlidetabs));
			}
			if (Configure::read('Saito.Settings.shoutbox_enabled') == false) {
				unset($slidetabs[array_search('slidetab_shoutbox', $slidetabs)]);
			}
			$this->set('slidetabs', $slidetabs);
		}

		protected function _beforeFilterAdminArea() {
			// protect the admin area
			if ($this->CurrentUser->isAdmin() !== true) :
				throw new ForbiddenException();
			endif;

			$this->layout = 'admin';
		}

		/**
		 * manually require auth and redirect cycle
		 */
		protected function _requireAuth() {
			$this->Session->setFlash(__('auth_autherror'), 'flash/warning');
			$here = $this->request->here(false);
			$this->Auth->redirectUrl($here);
			$this->redirect(['controller' => 'users', 'action' => 'login']);
		}

/**
 * Shows the disclaimer in the layout
 */
		protected function _showDisclaimer() {
			$this->_setAppStats();
			$this->set('showDisclaimer', true);
		}

/**
 * Set application statistics used in the disclaimer
 */
		protected function _setAppStats() {
			if ($this->_areAppStatsSet) {
				return;
			}
			Stopwatch::start('AppController->_setAppStats()');
			$this->_areAppStatsSet = true;

			$loggedinUsers = $this->User->UserOnline->getLoggedIn();
			$this->set('UsersOnline', $loggedinUsers);

			/* @var $headCounter array or false */
			$headCounter = Cache::read('header_counter', 'short');
			if (!$headCounter) {
				$countableItems = [
					'user_online' => ['model' => 'UserOnline', 'conditions' => ''],
					'user' => ['model' => 'User', 'conditions' => ''],
					'entries' => ['model' => 'Entry', 'conditions' => ''],
					'threads' => [
						'model' => 'Entry',
						'conditions' => ['pid' => 0]
					]
				];

				if (!isset($this->Entry)) {
					$this->loadModel('Entry');
				}

				// @td foreach not longer feasable, refactor
				foreach ($countableItems as $titel => $options) {
					if ($options['model'] === 'Entry') {
						$headCounter[$titel] = $this->{$options['model']}->find(
							'count',
							['contain' => false, 'conditions' => $options['conditions']]
						);
					} elseif ($options['model'] === 'User') {
						$headCounter[$titel] = $this->Entry->{$options['model']}->find(
							'count',
							['contain' => false, 'conditions' => $options['conditions']]
						);
					} elseif ($options['model'] === 'UserOnline') {
						$headCounter[$titel] = $this->Entry->User->{$options['model']}->find(
							'count',
							['contain' => false, 'conditions' => $options['conditions']]
						);
					}
				}
				$headCounter['latestUser'] = $this->Entry->User->find('latest');
				Cache::write('header_counter', $headCounter, 'short');
			}
			$headCounter['user_registered'] = count($loggedinUsers);
			$anonUser = $headCounter['user_online'] - $headCounter['user_registered'];
			// compensate for cached 'user_online' so that user_anonymous can't get negative
			$headCounter['user_anonymous'] = ($anonUser < 0) ? 0 : $anonUser;

			$this->set('HeaderCounter', $headCounter);
			Stopwatch::stop('AppController->_setAppStats()');
		}

		/**
		 * sets l10n .ctp file if available
		 */
		protected function _l10nRenderFile() {
			$locale = Configure::read('Config.language');
			$l10nViewPath = $this->viewPath . DS . $locale;
			$l10nViewFile = $l10nViewPath . DS . $this->view . '.ctp';
			if ($locale && file_exists(APP . 'View' . DS . $l10nViewFile)
			) {
				$this->viewPath = $l10nViewPath;
			}
		}

	}
