<?php

	App::uses('Component', 'Controller');

	/**
	 * CakePHP Detectors
	 *
	 * @copyright     Copyright 2012, Saito (http://saito.siezi.org/)
	 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
	 * @package 			Saito.Lib
	 */
	class DetectorsComponent extends Component {

		/**
		 * User agent snippets for bots
		 *
		 * @var array
		 */
		protected $_bots = [
			'bot',
			'crawl',
			'spider',
			'slurp',
			'archive',
			'baidu',
			'yandex',
			'search',
			'Mediapartners',
			'java',
			'wget',
			'curl',
			'Commons-HttpClient',
			'Python-urllib',
			'libwww',
			'httpunit',
			'nutch',
			'teoma',
			'webmon ',
			'httrack',
			'convera',
			'biglotron',
			'grub.org',
			'speedy',
			'fluffy',
			'bibnum.bnf',
			'findlink',
			'panscient',
			'IOI',
			'ips-agent',
			'yanga',
			'Voyager',
			'CyberPatrol',
			'postrank',
			'page2rss',
			'linkdex',
			'ezooms',
			'mail.ru',
			'heritrix',
			'findthatfile',
			'Aboundex',
			'summify',
			'ec2linkfinder',
			'facebook',
			'yeti',
			'RetrevoPageAnalyzer',
			'sogou',
			'wotbox',
			'ichiro',
			'drupact',
			'coccoc',
			'integromedb',
			'siteexplorer.info',
			'proximic',
			'changedetection'
		];

		protected $_isBot = null;

		public function initialize(Controller $Controller) {
			$Controller->request->addDetector('bot',
				['callback' => [$this, 'isBot']]
			);

			$Controller->request->addDetector('preview',
				['callback' => ['DetectorsComponent', 'isPreview']]
			);
		}

		/**
		 * Detects if a request is made by a spider/crawler
		 *
		 * @return bool
		 */
		public function isBot() {
			if ($this->_isBot === null) {
				$agent = env('HTTP_USER_AGENT');
				$imploded = implode('|', $this->_bots);
				$this->_isBot = (bool)preg_match('/' . $imploded . '/i', $agent);
			}
			return $this->_isBot;
		}

		/**
		 * Detect if a request was made by a browser preview feature
		 *
		 * Adds the isPreview() method to the controllers request object.
		 *
		 * Supports:
		 *
		 * - Safari top sites
		 * - Firefox prefetch
		 *
		 * @return bool
		 */
		public static function isPreview() {
			$_isPreview = (
					(env('HTTP_X_PURPOSE') === 'preview') // Safari
					|| (env('HTTP_X_MOZ') === 'prefetch') // Firefox
			);
			return $_isPreview;
		}

	}