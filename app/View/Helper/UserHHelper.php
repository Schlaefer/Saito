<?php

	use Saito\User\SaitoUser;

	App::uses('AppHelper', 'View/Helper');

	class UserHHelper extends AppHelper {

		protected $_SaitoUser = null;

		public $helpers = array(
			'Html',
			'Session',
		);

		public function beforeRender($viewFile) {
			parent::beforeRender($viewFile);
			$this->_SaitoUser = new SaitoUser();
		}

		public function banned($isBanned) {
			$out = '';
			if ($isBanned) :
				$out = '<i class="fa fa-ban fa-lg"></i>';
			endif;
			return $out;
		}

		/**
		 * generates CSS from user-preferences
		 *
		 * @param array $User
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
			// write out all __() strings for l10n
			switch ($type):
				case 'user':
					return __('user.type.user');
				case 'mod':
					return __('user.type.mod');
				case 'admin':
					return __('user.type.admin');
			endswitch;
		}

		/**
		 * Creates link to user contact page with image
		 *
		 * @param $user
		 * @return string
		 */
		public function contact($user) {
			$out = '';
			if ($user['personal_messages'] && is_string($user['user_email'])) {
				$out = $this->Html->link(
					'<i class="fa fa-envelope-o fa-lg"></i>',
					['controller' => 'contacts', 'action' => 'user', $user['id']],
					['escape' => false]);
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

		public function isMod($user) {
			$this->_SaitoUser->setSettings($user);
			return $this->_SaitoUser->isMod($user);
		}

		public function isAdmin($user) {
			$this->_SaitoUser->setSettings($user);
			return $this->_SaitoUser->isAdmin($user);
		}

	}
