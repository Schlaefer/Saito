<?php

	namespace Phile\Plugin\Siezi\PhileMarkdownEditor;

	use Phile\Core\ServiceLocator;
	use Phile\Core\Utility;
	use Phile\Exception;
	use Phile\Repository\Page;

	/**
	 * Markdown editor plugin for Phile
	 *
	 * @author Schlaefer <openmail+sourcecode@siezi.com>
	 * @link https://github.com/Schlaefer/phileMarkdownEditor
	 * @license http://opensource.org/licenses/MIT
	 * @package Phile\Plugin\Siezi\PhileMarkdownEditor
	 */

	class Plugin extends \Phile\Plugin\AbstractPlugin implements \Phile\Gateway\EventObserverInterface {

		protected $_allowedActions = [
			'login',
			'logout',
			'password'
		];

		/**
		 * @var Auth
		 */
		protected $_Auth;

		protected $_Request;

		protected $_Response;

		protected $_phile;

		protected $_pluginPath;

		protected $_TemplateEngine;

		public function __construct() {
			\Phile\Event::registerEvent('request_uri', $this);
			\Phile\Event::registerEvent('template_engine_registered', $this);

			$this->_pluginPath = dirname(dirname(__FILE__));
			$this->_Request = new Request($_REQUEST, $this->settings['uri']);
		}

		public function on($eventKey, $data = null) {
			if ($eventKey === 'request_uri') {
				$this->_Request->setUri($data['uri']);
				$this->_Request->setBase($this->settings['uri']);
				return;
			}

			// current page is not an editor page
			if (!$this->_Request->isEditor()) {
				return;
			}

			if ($eventKey === 'template_engine_registered') {
				$this->_phile = $data['data'];
				$this->_Auth = new Auth($this->_Request, $this->settings['password']);
				$this->_Response = new Response($this->_phile['base_url'],
					$this->settings['uri']);

				$loader = new \Twig_Loader_Filesystem($this->_pluginPath);
				$this->_TemplateEngine = new \Twig_Environment($loader, $this->_phile);

				$this->_dispatch();
			}
		}

		public function editor() {
			//= setup menuPages
			$PageRepository = new Page();
			$menuPages = $PageRepository->findAll();
			$navData = [];
			foreach ($menuPages as $page) {
				$navData[] = [
					'title' => $page->getTitle(),
					'url' => $page->getUrl()
				];
			}

			$appSettings = [
				'baseUrl' => Utility::getBaseUrl()
			];

			$data = [
				'appSettings' => json_encode($appSettings),
				'navData' => json_encode($navData),
			];

			$this->_render('editor', $data);
		}

		public function login() {
			$data['authEnabled'] = $this->_Auth->authEnabled();
			$this->_render('login', $data);
		}

		public function logout() {
			$this->_Auth->logout();
			$this->_Response->redirect('login');
		}

		public function create() {
			try {
				$title = $this->_Request->param('title');
				$content = '<!--
	Title: ' . $title . '
	Author:
	Date: ' . date('Y-m-d') . '
	-->

	';

				$file = new ContentFile();
				$file->create($title, $content);
				$body = [
					'title' => $title,
					'content' => $content,
					'url' => $file->getFilename(),
				];
			} catch (Exception $e) {
				$this->_Response->setStatusCode(400);
				$body = ['error' => $e->getMessage()];
			}

			$this->_Response->type('json');
			$this->_Response->body = json_encode($body);
		}

		public function destroy() {
			$title = $this->_Request->param('file');
			$file = new ContentFile($title);
			$file->delete();
		}

		public function open() {
			$title = $this->_Request->param('file');
			$file = new ContentFile($title);
			$this->_Response->body = $file->read();
		}

		public function save() {
			$content = $this->_Request->param('content');
			if (!$content) {
				throw new Exception();
			}
			$url = $this->_Request->param('show');
			$file = new ContentFile($url);
			$file->write($content);
			$this->_clearPageCache($file);

			$this->_Response->type('json');
			$this->_Response->body = json_encode(
				['content' => $content]
			);
		}

		public function password() {
			$data = [];
			$passwordHash = $this->_Request->param('passwordToHash');
			if ($passwordHash) {
				$data = [
					'hashedPassword' => $this->_Auth->hash($passwordHash)
				];
			}
			$this->_render('password', $data);
		}

		public function test() {
			$this->_render('test');
		}

		/**
		 * clears page cache
		 *
		 * @param ContentFile $File
		 * @throws Exception\ServiceLocatorException
		 */
		protected function _clearPageCache(ContentFile $File) {
			if (ServiceLocator::hasService('Phile_Cache')) {
				$fullPath = $File->getFullPath();
				$cache = ServiceLocator::getService('Phile_Cache');
				$key = 'Phile_Model_Page_' . md5($fullPath);
				$cache->delete($key);
			}
		}

		protected function _dispatch() {
			$action = $this->_Request->getAction();

			if ($action === '/') {
				$this->_Response->redirect('editor');
			}

			$reflection = new \ReflectionMethod($this, $action);
			if ($action === 'on' || !$reflection->isPublic()) {
				// page not found
				return;
			}

			$authorized = $this->_Auth->auth();
			if (in_array($action, $this->_allowedActions)) {
				if ($action === 'login' && $authorized) {
					$this->_Response->redirect('editor');
				}
			} elseif (!$authorized) {
				$this->_Response->redirect('login');
			}

			$this->$action();
			$this->_Response->send();
		}

		protected function _render($file, $vars = []) {
			//= setup other view vars
			$vars += $this->_phile;
			$vars += [
				'pluginUrl' => $this->_phile['base_url'] . '/plugins/siezi/phileMarkdownEditor'
			];

			//= render
			$this->_Response->body = $this->_TemplateEngine->render(
				'pages' . DIRECTORY_SEPARATOR . $file . '.twig', $vars);
		}


	}