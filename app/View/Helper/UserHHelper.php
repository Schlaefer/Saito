<?php

	App::import('Lib', 'SaitoUser');
	App::uses('AppHelper', 'View/Helper');

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

	class UserHHelper extends AppHelper {

		protected $_userranks;

		protected $_SaitoUser = null;

		public $helpers = array(
			'Html',
			'Session',
		);

		public function beforeRender($viewFile) {
			parent::beforeRender($viewFile);
			$this->_userranks = Configure::read('Saito.Settings.userranks_ranks');
		}

		public function banned($isBanned) {
			$out = '';
			if ($isBanned) :
				$out = '<i class="fa fa-ban fa-lg"></i>';
			endif;
			return $out;
		}

/**
 * generates the JavaSript commands to format the views according to user prefs
 *
 * @param null $User
 * @return string
 */
		public function generateCss(array $User) {
			$_styles = [];

			// colors
			$_cNew = $User['user_color_new_postings'];
			$_cOld = $User['user_color_old_postings'];
			$_cAct = $User['user_color_actual_posting'];

			$_aMetatags = ['', ':link', ':visited', ':hover', ':active'];
			foreach ($_aMetatags as $_aMetatag) {
				if (!empty($_cOld) && $_cOld !== '#') {
					$_styles[] = ".et-root .et$_aMetatag, .et-reply .et$_aMetatag	{ color: $_cOld; }";
				}
				if (!empty($_cNew) && $_cNew !== '#') {
					$_styles[] = ".et-new .et$_aMetatag { color: $_cNew; }";
				}
				if (!empty($_cAct) && $_cAct !== '#') {
					$_styles[] = ".et-current .et$_aMetatag { color: $_cAct; }";
				}
			}

			return '<style type="text/css">' . implode(" ", $_styles) . '</style>';
		}

/**
 * Translates user types
 *
 * @param $type
 * @return mixed
 */
		public function type($type) {
			# we could do this cleverer, but we want to write
			# all strings explicitly for Poedit
			switch ($type):
				case 'user':
					return __('ud_user');
				case 'mod':
					return __('ud_mod');
				case 'admin':
					return __('ud_admin');
			endswitch;
		}

/**
 * Creates link to user contanct page with image
 *
 * @param $user
 * @return string
 */
		public function contact($user) {
			$out = '';
			if ($user['personal_messages'] && is_string($user['user_email'])) {
				$out = $this->Html->link(
					'<i class="fa fa-envelope-o fa-lg"></i>',
					array('controller' => 'users', 'action' => 'contact', $user['id']),
					array('escape' => false));
			}
			return $out;
		}

/**
 * Creates Homepage Links with Image from Url
 * @param <type> $url
 * @return <type>
 */
		public function homepage($url) {
			$out = $url;
			if (is_string($url)) {
				if (substr($url, 0, 4) == 'www.') {
					$url = 'http://' . $url;
				}
				if (substr($url, 0, 4) == 'http') {
					$out = $this->Html->link(
						'<i class="fa fa-home fa-lg"></i>',
						$url,
						array('escape' => false));
				} else {
					$out = h($url);
				}
			}
			return $out;
		}

/**
 * calculates user rank depending on posting_count
 *
 * @param int $_numberOfPostings
 * @return mixed
 */
		public function userRank($_numberOfPostings = 0) {
			$out = __('userranks_not_found');
			foreach ($this->_userranks as $treshold => $rank) :
				$out = $rank;
				if ($_numberOfPostings <= $treshold) :
					break;
				endif;
			endforeach;
			return $out;
		}

		public function isMod($user) {
			// @td fix this fubar
			$this->_saitoUserFactory();
			$this->_SaitoUser->set($user);
			return $this->_SaitoUser->isMod($user);
		}

		public function isAdmin($user) {
			// @td fix this fubar
			$this->_saitoUserFactory();
			$this->_SaitoUser->set($user);
			return $this->_SaitoUser->isAdmin($user);
		}

		protected function _saitoUserFactory() {
			if ($this->_SaitoUser === null) :
				$this->_SaitoUser = new SaitoUser(new ComponentCollection());
			endif;
		}

	}
