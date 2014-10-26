<?php

	App::uses('SaitoEventListener', 'Lib/Saito/Event');

	class FlattrRenderer implements SaitoEventListener {

		const API_URL = '//api.flattr.com/';

		protected $_Controller;

		protected $_settings;

		protected $_View;

		public function __construct($settings) {
			$this->_settings = $settings;
		}

		public function implementedSaitoEvents() {
			return [
				'Event.Saito.Controller.initialize' => 'initController',
				'Event.Saito.Model.initialize' => 'initModels',
				'Request.Saito.View.Posting.addForm' => 'postingAdd',
				'Request.Saito.View.Posting.footerActions' => 'postingView',
				'Request.Saito.View.User.beforeFullProfile' => 'userView',
				'Request.Saito.View.User.edit' => 'userEdit'
			];
		}

		public function initController($eventData) {
			$this->_Controller = $eventData['subject'];
		}

		public function initModels($eventData) {
			$Model = $eventData['Model'];
			if ($Model->alias !== 'Entry') {
				return;
			}

			//= output additional data when viewing a posting
			foreach (['User.flattr_uid', 'Entry.flattr'] as $key) {
				$Model->showEntryFieldListAdditional[$key] = $key;
			}

			//= allows flattr field in Entry::create() and Entry::update()
			$Model->allowedInputFields['create'][] = 'flattr';
			$Model->allowedInputFields['update'][] = 'flattr';
		}

		public function userEdit($eventData) {
			return $eventData['View']->element('Flattr.userEdit');
		}

		public function userView($eventData) {
			$user = $eventData['user']['User'];
			if (empty($user['flattr_uid']) || !$user['flattr_allow_user']) {
				return;
			}

			if (!$this->_View) {
				$this->_View = $eventData['View'];
			}

			$title = __d('flattr', 'flattr');
			$content = $this->button('', [
					'uid' => $user['flattr_uid'],
					'language' => $this->_settings['language'],
					'title' => '[' . $_SERVER['HTTP_HOST'] . '] ' . $user['username'],
					'description' => '[' . $_SERVER['HTTP_HOST'] . '] ' . $user['username'],
					'cat' => $this->_settings['category'],
					'button' => 'compact'
				]
			);
			return compact('title', 'content');
		}

		public function postingAdd($eventData) {
				// @performance ?
				$categoryFlattr = $this->_Controller->Entry->Category->find(
					'list',
					['conditions' => 'accession = 0', 'fields' => ['id']]
				);
			return $eventData['View']->element('Flattr.postingAdd', ['category_flattr' => $categoryFlattr]);
		}

		public function postingView($eventData) {
			$entry = $eventData['posting']['Entry'];
			$user = $eventData['posting']['User'];
			if (empty($user['flattr_uid']) || !$entry['flattr']) {
				return;
			}

			if (!$this->_View) {
				$this->_View = $eventData['View'];
			}

			return $this->button('',
				array(
					'uid' => $user['flattr_uid'],
					'language' => $this->_settings['language'],
					'title' => $entry['subject'],
					'description' => $entry['subject'],
					'cat' => $this->_settings['category'],
					'button' => 'compact',
				)
			);
		}

		/**
		 * Flattr Donate Button
		 *
		 * flattr helper basierend auf http://bakery.cakephp.org/articles/wyrihaximus/2010/06/05/flattr-helper mit [Update aus Kommentar](http://www.dereuromark.de/2010/12/20/flattr-cakephp-1-3-helper/)
		 *
		 * @link http://flattr.com/support/integrate/js
		 * @param mixed $url (unique! neccessary)
		 * @param array $options
		 * @param array $attr
		 * @return string
		 */
		public function button($url, $options = array(), $attr = array()) {
			if (empty($options['uid'])) {
				$options['uid'] = Configure::read('Flattr.uid');
			}
			$categories = array();

			$defaults = array(
				'mode' => 'auto',
				'description' => $_SERVER['HTTP_HOST'],
				'language' => 'en_US',
				'category' => 'text',
				'button' => 'default', # none or compact
				'tags' => array(),
				//'hidden' => '',
				//'description' => '',
			);
			$options = array_merge($defaults, $options);

			$mode = $options['mode'];
			unset($options['mode']);
			if (is_array($options['tags'])) {
				$options['tags'] = implode(',', $options['tags']);
			}

			$description = $options['description'];
			unset($options['description']);

			$rev = array();
			foreach ($options as $key => $option) {
				$rev[] = $key . ':' . $option;
			}
			$linkOptions = array(
				'title' => $_SERVER['HTTP_HOST'],
				'class' => 'FlattrButton',
				'style' => 'display:none;',
				'rel' => 'flattr;' . implode(';', $rev)
			);
			$linkOptions = array_merge($linkOptions, $attr);

			$js = "(function() {
    var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
    s.type = 'text/javascript';
    s.async = true;
    s.src = 'http" . ((env('HTTPS')) ? 's:' : ':') . self::API_URL . "js/0.6/load.js?mode=" . $mode . "';
    t.parentNode.insertBefore(s, t);
})();";
			$code = $this->_View->Html->link($description,
				$this->_View->Html->url($url, true),
				$linkOptions);
			$code .= $this->_View->Html->scriptBlock($js, array('inline' => true));
			return $code;
		}

	}
