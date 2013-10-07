<?php

	App::uses('Component', 'Controller');
	App::uses('CakeEmail', 'Network/Email');

	class SaitoEmailComponent extends Component {

		protected $_emailConfigExists = false;

		protected $_User = null;

		protected $_config = array();

		protected $_viewVars = array();

		protected $_recipient = null;

		protected $_sender = null;

		protected $_CakeEmail = null;

		protected $_webroot;

		protected $_appAddress;

		public function startup(Controller $Controller) {
			$this->_webroot = $Controller->request->webroot;
			$this->_User = $Controller->User;
			$this->_CakeEmail = new CakeEmail();
			$this->_appAddress = array(
				Configure::read('Saito.Settings.forum_email') => Configure::read('Saito.Settings.forum_name'));
		}

/**
 *
 * $options = array(
 * 		'recipient' // user-id or ['User']
 * 		'sender'		// user-id or ['User']
 * 		'ccsender' // if true send carbon-copy to sender
 * 		'template'
 * 		'message'
 * 		'viewVars'
 * );
 *
 * @param array $options
 * @throws Exception
 */
		public function email($options = array()) {
			$this->_resetConfig();
			$this->_config($options);
			$this->_send($this->_config, $this->_viewVars);

			if (isset($options['ccsender']) && $options['ccsender'] === true) :
				$this->_sendCopyToOriginalSender($this->_config, $this->_viewVars);
			endif;
		}

		protected function _resetConfig() {
			$this->_config = array();
			$this->_viewVars = array();
			$this->_CakeEmail->reset();
		}

		protected function _config($options = array()) {
			$defaults = array(
				'viewVars' => array(
					'webroot' => Router::fullBaseUrl() . $this->_webroot,
				),
			);
			extract(array_merge_recursive($defaults, $options));

			if ($this->_emailConfigExists || file_exists(APP . 'Config' . DS . 'email' . '.php')) {
				// set base config from app/config/email.php
				$this->_CakeEmail->config('saito');
				// set the forum app address from email.php
				if ($this->_CakeEmail->from()) {
					$this->_appAddress = $this->_CakeEmail->from();
					// set app address name to forum's name if it's not set in email.php
					if ((is_array($this->_appAddress) && key($this->_appAddress) === current($this->_appAddress))) {
						$this->_appAddress = array(
							key($this->_appAddress) => Configure::read('Saito.Settings.forum_name'));
					}
				}
				$this->_emailConfigExists = true;
			}

			// get users involved in email
			foreach (array('recipient', 'sender') as $person) {
				if (is_array($$person)) {
					$this->{'_' . $person} = $$person;
				} else {
					$this->_User->id = $$person;
					$this->_User->contain();
					$this->{'_' . $person} = $this->_User->read();
					if ($this->{'_' . $person} == false) {
						throw new Exception("Can't find $person for email.");
					}
				}
			}

			$this->_config = array(
				'from' => array($this->_sender['User']['user_email'] => $this->_sender['User']['username']),
				'to' => $this->_recipient['User']['user_email'],
				'subject' => $subject,
				'emailFormat' => 'text',
				'sender' => $this->_appAddress,
			);

			if (isset($template)) :
				$this->_config['template'] = $template;
			endif;

			if (Configure::read('debug') > 2) {
				$this->_config['transport'] = 'Debug';
				$this->_config['log'] = true;
			};

			if (isset($message)):
				$this->_viewVars['message'] = $message;
			endif;
			$this->_viewVars += $viewVars;
		}

/**
 * Sends a copy of a completely configured email to the author
 *
 * @param $config
 * @param $viewVars
 */
		protected function _sendCopyToOriginalSender($config, $viewVars) {
			// use name for recipient if available
			if (!empty($this->_recipient['User']['username'])) {
				$emailConfig['to'] = array(
					$this->_recipient['User']['user_email'] => $this->_recipient['User']['username']
				);
			}

			// set new subject
			$data = array('subject' => $config['subject']);
			if (is_array($config['to'])) {
				$data['recipient-name'] = current($config['to']);
				$str = __('Copy of your message: ":subject" to ":recipient-name"');
			} else {
				$str = __('Copy of your message: ":subject"');
			}
			$config['subject'] = String::insert($str, $data);

			// set new addresses
			$config['to'] = $config['from'];
			$config['from'] = $this->_appAddress;

			$this->_send($config, $viewVars);
		}

/**
 * Sends the completely configured email
 *
 * @param $config
 * @param $viewVars
 */
		protected function _send($config, $viewVars) {
			$email = $this->_CakeEmail;
			// workaround for http://cakephp.lighthouseapp.com/projects/42648/tickets/2855-cakeemail-transports-have-ambiguous-config-behaviors
			$email->config(array_merge($this->_CakeEmail->config(), $config));
			$email->viewVars($viewVars);
			if (!env('TRAVIS')) {
				$email->send();
			}
		}

	}