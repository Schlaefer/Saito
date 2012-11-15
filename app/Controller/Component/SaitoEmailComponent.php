<?php

	App::uses('Component', 'Controller');
	App::uses('CakeEmail', 'Network/Email');

	class SaitoEmailComponent extends Component {

		protected $User = null;

		protected $_config = array();

		protected $_webroot;

		protected $_app_address;

		public function startup (Controller $Controller) {
			$this->_webroot = $Controller->request->webroot;
			$this->User = $Controller->User;
			$this->_app_address = array(
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
		 * @param type $options
		 * @throws Exception
		 */
		public function email($options = array()) {
			$defaults = array(
					'viewVars'=> array(
							'webroot' => FULL_BASE_URL . $this->_webroot,
					),
			);
			extract(array_merge_recursive($defaults, $options));

			if (!is_array($recipient)) {
				$this->User->id = $recipient;
				$this->User->contain();
				$recipient = $this->User->read();
				if($recipient == false) {
					throw new Exception('Can\'t find recipient for email.');
				}
			}
			if (!is_array($sender)) {
				$this->User->id = $sender;
				$this->User->contain();
				$sender = $this->User->read();
				if($sender == false) {
					throw new Exception('Can\'t find sender for email.');
				}
			}

			$emailConfig = array(
							'from'	=> array($sender['User']['user_email'] => $sender['User']['username']),
							'to'          => $recipient['User']['user_email'],
							'subject'     => $subject,
							'emailFormat' => 'text',
							'sender'      => $this->_app_address,
						);

			if (isset($template)) :
				$emailConfig['template'] = $template;
			endif;

			if (Configure::read('debug') > 2) :
				$emailConfig['transport'] = 'Debug';
				$emailConfig['log'] 			= true;
			endif;

			if (isset($message)):
				$viewVars['message'] = $message;
			endif;

			$this->_send($emailConfig, $viewVars);

			if (isset($ccsender) && $ccsender === true) :
				$this->_sendCopyToOriginalSender($emailConfig, $viewVars);
			endif;
		}

		protected function _sendCopyToOriginalSender($config, $view_vars) {
			$config['to'] = $config['from'];
			$config['from'] = $this->_app_address;

			$str = __('Copy of your message: :subject');
			$data = array('subject' => $config['subject']);
			$config['subject'] = String::insert($str, $data);
			$this->_send($config, $view_vars);
		}

		protected function _send($config, $view_vars) {
			$email = new CakeEmail();
			$email->config($config);
			$email->viewVars($view_vars);
			$email->send();
		}

	}
