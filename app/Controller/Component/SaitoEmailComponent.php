<?php

	App::uses('Component', 'Controller');
	App::uses('CakeEmail', 'Network/Email');

	class SaitoEmailComponent extends Component {

		protected $_emailConfigExists = null;

		protected $_User = null;

		protected $_config = array();

		protected $_viewVars = array();

		/**
		 * @var array ['User' => ['username' => , 'user_email' =>]
		 */
		protected $_recipient = null;

		/**
		 * @var array ['User' => ['username' => , 'user_email' =>]
		 */
		protected $_sender = null;

		/**
		 * @var CakeEmail
		 */
		protected $_CakeEmail;

		protected $_webroot;

		protected $_appAddress;

		protected $_addresses;

		protected $_forumName;

		protected $_headerSent;

		protected $_predefined = ['contact', 'main', 'register', 'system'];

		public function startup(Controller $Controller) {
			$this->_webroot = $Controller->request->webroot;
			$this->_User = $Controller->User;
		}

		/**
		 * init only if mail is actually send during request
		 *
		 * @throws InvalidArgumentException
		 */
		protected function _init() {
			if ($this->_addresses !== null) {
				return;
			}

			$this->_addresses = [
				'main' => Configure::read('Saito.Settings.forum_email'),
				'contact' => Configure::read('Saito.Settings.email_contact'),
				'register' => Configure::read('Saito.Settings.email_register'),
				'system' => Configure::read('Saito.Settings.email_system')
			];

			foreach ($this->_addresses as $title => $address) {
				if (empty($address)) {
					throw new InvalidArgumentException("Email address not set: $title");
				}
			}

			$this->_emailConfigExists = file_exists(APP . 'Config' . DS . 'email' . '.php');

			$this->_forumName = Configure::read('Saito.Settings.forum_name');

			$this->_CakeEmail = new CakeEmail();
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
 * @return array
 */
		public function email($options = array()) {
			$this->_init();
			$this->_resetConfig();
			$this->_config($options);
			$result = $this->_send($this->_config, $this->_viewVars);

			if (isset($options['ccsender']) && $options['ccsender'] === true) {
				$result = $this->_sendCopyToOriginalSender($this->_config,
					$this->_viewVars);
			}

			return $result;
		}

		protected function _resetConfig() {
			$this->_config = [];
			$this->_viewVars = [];
			$this->_CakeEmail->reset();
		}

		protected function _config($params = []) {
			$defaults = [
				'viewVars' => [
					'webroot' => Router::fullBaseUrl() . $this->_webroot,
				],
			];
			$params = array_merge_recursive($defaults, $params);

			$this->_initConfigFromFile();

			$this->_sender = $this->_getSender($params['sender']);
			$this->_recipient = $this->_getRecipient($params['recipient']);

			$this->_config = [
				'from' => $this->_pA($this->_sender,
						$this->_sender['User']['username']),
				'to' => $this->_recipient['User']['user_email'],
				'subject' => $params['subject'],
				'emailFormat' => 'text',
			];

			//# set 'sender' header
			$headerSender = $this->_getHeaderSender();
			if ($headerSender) {
				$this->_config['sender'] = $headerSender;
			}

			if (isset($params['template'])) {
				$this->_config['template'] = $params['template'];
			}

			if (isset($params['message'])) {
				$this->_viewVars['message'] = $params['message'];
			}
			$this->_viewVars += $params['viewVars'];

			$this->_configTransport();
		}

		protected function _configTransport() {
			if (Configure::read('debug') > 2 || Configure::read('Saito.Debug.email')) {
				$this->_config['transport'] = 'Debug';
			};
			if (Configure::read('debug') > 2) {
				$this->_config['log'] = true;
			};
		}

		protected function _getHeaderSender() {
			if ($this->_emailConfigExists && $this->_CakeEmail->from()) {
				// set the forum app address from email.php
				$hs = $this->_CakeEmail->from();

				// set app address name to forum's name if it's not set in email.php
				if ((is_array($hs) && key($hs) === current($hs))) {
					$hs = $this->_pA(key($hs));
				}
			} elseif ($this->_headerSent) {
				$type = $this->_headerSent;
				$hs = $this->_pA($this->_addresses[$type]);
			} else {
				$hs = false;
			}
			return $hs;
		}

		/**
		 * returns participant array (Cake mail array)
		 *
		 * @param $address string with address or ['User']-sender/recipient
		 * @param $name
		 * @return array [<address> => <name>]
		 */
		protected function _pA($address, $name = null) {
			if (is_array($address) && isset($address['User'])) {
				$name = $address['User']['username'];
				$address = $address['User']['user_email'];
			}
			if ($name === null) {
				$name = $this->_forumName;
			}
			return [$address => $name];
		}

		/**
		 * set base config from app/config/email.php
		 */
		protected function _initConfigFromFile() {
			if (!$this->_emailConfigExists) {
				return;
			}
			$this->_CakeEmail->config('saito');
		}

		public function getPredefinedSender($type) {
			$this->_init();
			return ['User' => [
				'username' => $this->_forumName,
				'user_email' => $this->_addresses[$type]
			]];
		}

		protected function _getRecipient($recipient) {
			return $this->_getParticipant($recipient);
		}

		protected function _getSender($sender) {
			if (!is_string($sender) ||
					!in_array($sender, $this->_predefined)
			) {
				// sender-address does not belong to system: is external address
				// and should be send 'in behalf off'
				$this->_headerSent = 'system';
			}
			return $this->_getParticipant($sender);
		}

		/**
		 * @param $value
		 * @return array
		 * @throws Exception
		 */
		protected function _getParticipant($value) {
			//# participant-address is valid address
			if (is_array($value)) {
				return $value;
			}

			//# participant-address belongs to system
			if (is_string($value) &&
				in_array($value, $this->_predefined)
			) {
				return $this->getPredefinedSender($value);
			}

			//# participant-address belongs to external user
			$this->_User->id = $value;
			$this->_User->contain();
			$participant = $this->_User->read();

			if (empty($participant)) {
				throw new Exception("Can't find participant for email.");
			}

			return $participant;
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
				$emailConfig['to'] = $this->_pA($this->_recipient['User']['user_email'],
					$this->_recipient['User']['username']);
			}

			// set new subject
			$data = array('subject' => $config['subject']);
			if (is_array($config['to'])) {
				$data['recipient-name'] = current($config['to']);
				$str = __('Copy of your message: ":subject" to ":recipient-name"');
			} else {
				$str = __('Copy of your message: ":subject"');
			}
			$config['subject'] = CakeText::insert($str, $data);

			// set new addresses
			$config['to'] = $config['from'];
			// @todo should be system message
			$config['from'] = $this->_pA($this->getPredefinedSender('system'));

			// CC is always send by system
			unset($config['sender']);

			return $this->_send($config, $viewVars);
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
			return $email->send();
		}

	}