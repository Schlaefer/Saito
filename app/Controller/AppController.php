<?php

App::uses('Controller', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Lib', 'Stopwatch.Stopwatch');

if (Configure::read('debug') > 0) {
	App::uses('FireCake', 'DebugKit.Lib');
}

class AppController extends Controller {
	public $components = array (
			// 'DebugKit.Toolbar',

			'Auth',
			'Bbcode' => array(
				'hashBaseUrl' => 'entries/view/',
				'atBaseUrl'   => 'users/name/',
			),

			/**
			 * You have to have Cookie before CurrentUser to have the salt initialized.
			 * Check by deleting Session cookie when persistent cookie is present.
			 * @td maybe bug in Cake, because Cookies should be initialized in CurrentUser's $components
			 */
			'Cookie',
			'CurrentUser',
			'JsData',
			'SaitoEmail',
      'EmailNotification',
			// Enabling data view for rss/xml and json
			'RequestHandler',
			'Session',
			'PreviewDetector.PreviewDetector'
	);
	public $helpers = array (
			'JsData',
			// 'Markitup.Markitup',
			'Layout',
			'RequireJs',
			'Stopwatch.Stopwatch',
			'TextH',
			'TimeH',
			'UserH',
			// CakePHP helpers
			'Js' => array('Jquery'),
			'Html',
			'Form',
			'Session',
	);

	public $uses = array (
			'Setting',
			'User',
	);

	/**
	 * name of the theme used
	 *
	 * @var string
	 */
	public $theme	= 'default';


	/**
	 * S(l)idetabs used by the application
	 *
	 * @var array
	 */
	public $installedSlidetabs = array(
		'slidetab_userlist',
		'slidetab_recentposts',
		'slidetab_recententries',
		'slidetab_shoutbox'
	);

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

//	var $persistModel = true;

	public function __construct($request = null, $response = null) {
		Stopwatch::start('---------------------- Controller ----------------------');
		parent::__construct($request, $response);
	}

	public function beforeFilter() {
		parent::beforeFilter();
		Stopwatch::start('App->beforeFilter()');

		// must be set before forum_disabled switch;
		$this->theme = Configure::read('Saito.theme');

		// Load forum settings
		$this->Setting->load(Configure::read('Saito.Settings'));

		// activate stopwatch in debug mode
		$this->set('showStopwatchOutput', false);
		if ((int)Configure::read('debug') > 0) {
			$this->set('showStopwatchOutput', true);
		};

		// setup for admin area
		if ( isset($this->params['admin']) ):
			$this->_beforeFilterAdminArea();
		endif;

		// disable forum with admin pref
		if ( Configure::read('Saito.Settings.forum_disabled') && !($this->params['action'] === 'login') ):
				if ( $this->CurrentUser->isAdmin() !== true ):
					return $this->render('/Pages/forum_disabled', 'barebone');
        endif;
    endif;

		$this->_setupSlideTabs();

		$this->_setConfigurationFromGetParams();
		if ($this->modelClass) {
			$this->{$this->modelClass}->setCurrentUser($this->CurrentUser);
		}

		// allow sql explain for DebugKit toolbar
		if ($this->request->plugin === 'debug_kit') {
			$this->Auth->allow('sql_explain');
		}

		Stopwatch::stop('App->beforeFilter()');
	} // end beforeFilter()

	public function beforeRender() {
		parent::beforeRender();

		Stopwatch::start('App->beforeRender()');

		if ($this->showDisclaimer) {
			$this->_showDisclaimer();
		}

    $this->set('lastAction', $this->localReferer('action'));
    $this->set('lastController', $this->localReferer('controller'));
		$this->set('isDebug', (int)Configure::read('debug') > 0);
		$this->_setTitleForLayout();

		Stopwatch::stop('App->beforeRender()');
		Stopwatch::start('---------------------- Rendering ---------------------- ');
	}

		/**
		 * Set forum configuration from get params in url
		 */
		protected function _setConfigurationFromGetParams() {

			if ($this->CurrentUser->isLoggedIn()) {
				// testing different themes on the fly with `theme` GET param /theme:<foo>/
				if (isset($this->passedArgs['theme'])):
					$this->theme = $this->passedArgs['theme'];
				endif;

				// activate stopwatch
				if (isset($this->passedArgs['stopwatch']) && Configure::read('Saito.Settings.stopwatch_get')) {
					$this->set('showStopwatchOutput', true);
				};

				// change language
				if (isset($this->passedArgs['lang'])) {
					$L10n = ClassRegistry::init('L10n');
					if ($L10n->catalog($this->passedArgs['lang'])) {
						// $this->Session->write('Config.language', $this->passedArgs['lang']);
						Configure::write('Config.language', $this->passedArgs['lang']);
					}
				};
			}
		}

		/**
		 * sets title for pages
		 *
		 * set in i18n domain file 'page_titles.po' with 'controller/view' title
		 *
		 * use plural for for controller title: 'entries/index' (not 'entry/index')!
		 *
		 * @td helper?
		 *
		 */
		protected function _setTitleForLayout() {
			$forumTitle = Configure::read('Saito.Settings.forum_name');
			if (empty($forumTitle)) {
				return;
			}

			$pageTitle = null;
			if (isset($this->viewVars['title_for_layout'])) {
				$pageTitle = $this->viewVars['title_for_layout'];
			} else {
				$pageTitle = __d(
					'page_titles',
					$this->params['controller'] . '/' . $this->params['action']
				);
			}

			if (!empty($pageTitle)) {
				$forumTitle = $pageTitle . ' – ' . $forumTitle;
			}

			$this->set('title_for_layout', $forumTitle);
		}

		public function initBbcode() {
			$this->_loadSmilies();
			$this->Bbcode->initHelper();
		}

		# @td make model function:
		#   @td must be reloaded somewherewhen updated
		# 	@td user cakephp cachen?
		protected function _loadSmilies() {
			if (Configure::read('Saito.Smilies.smilies_all') === null) {
				$smilies = ClassRegistry::init('Smiley');
				$smilies->load();
			}
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
		if ( isset($parsed[$type]) ):
			return $parsed[$type];
		else:
			if ( $type === 'action' ):
				return 'index';
			elseif ( $type === 'controller' ):
				return 'entries';
			endif;
		endif;
		return $referer;
	}

	/**
	 * Setup which slidetabs are available and user sorting
	 */
	protected function _setupSlideTabs() {
		$slidetabs = $this->installedSlidetabs;

		if (!empty($this->CurrentUser['slidetab_order'])) {
			$slidetabs_user = unserialize($this->CurrentUser['slidetab_order']);
			// disabled tabs still set in user-prefs are unset
			$slidetabs_user = array_intersect($slidetabs_user, $this->installedSlidetabs);
			// new tabs not set in user-prefs are added
			$slidetabs = array_unique(array_merge($slidetabs_user, $this->installedSlidetabs));
		}
		if (Configure::read('Saito.Settings.shoutbox_enabled') == false) {
			unset($slidetabs[array_search('slidetab_shoutbox', $slidetabs)]);
		}
		$this->set('slidetabs', $slidetabs);
	}

	protected function _beforeFilterAdminArea() {
    // protect the admin area
    if ( $this->CurrentUser->isAdmin() !== true ) :
      throw new ForbiddenException();
    endif;

		$this->layout = 'admin';
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
			if($this->_areAppStatsSet) {
				return;
			}
			Stopwatch::start('AppController->_setAppStats()');
			$this->_areAppStatsSet = true;

			$loggedin_users = $this->User->UserOnline->getLoggedIn();
			$this->set('UsersOnline', $loggedin_users);

			/* @var $header_counter array or false */
			$header_counter = Cache::read('header_counter', 'short');
			if (!$header_counter) {
				$countable_items = array(
						'user_online' => array('model'			 => 'UserOnline', 'conditions' => ''),
						'user'			 => array('model'			 => 'User', 'conditions' => ''),
						'entries'		 => array('model'			 => 'Entry', 'conditions' => ''),
						'threads'		 => array('model'			 => 'Entry', 'conditions' => array('pid' => 0)),
				);

				if (!isset($this->Entry)) {
					$this->loadModel('Entry');
				}

				// @td foreach not longer feasable, refactor
				foreach ($countable_items as $titel => $options) {
					if ($options['model'] === 'Entry') {
						$header_counter[$titel] = $this->{$options['model']}->find('count',
								array('contain'		 => false, 'conditions' => $options['conditions']));
					} elseif ($options['model'] === 'User') {
						$header_counter[$titel] = $this->Entry->{$options['model']}->find('count',
								array('contain'		 => false, 'conditions' => $options['conditions']));
					} elseif ($options['model'] === 'UserOnline') {
						$header_counter[$titel] = $this->Entry->User->{$options['model']}->find('count',
								array('contain' => false, 'conditions' => $options['conditions']));
					}
				}
				Cache::write('header_counter', $header_counter, 'short');
			}
			$header_counter['user_registered'] = count($loggedin_users);
			$anon_user												 = $header_counter['user_online'] - $header_counter['user_registered'];
			// compensate for cached 'user_online' so that user_anonymous can't get negative
			$header_counter['user_anonymous']	 = ($anon_user < 0) ? 0 : $anon_user;

			$this->set('HeaderCounter', $header_counter);
			Stopwatch::stop('AppController->_setAppStats()');
		}

}