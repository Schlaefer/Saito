<?php
	App::uses('AppHelper', 'View/Helper');
	App::uses('PostingViewTrait', 'Lib/Thread');

	# @td refactor helper name to 'EntryHelper'
	/**
	 * @package saito_entry
	 */

	class EntryHHelper extends AppHelper {

		use PostingViewTrait;

		public $helpers = array(
				'Form',
				'Html',
				'Session',
				'TimeH',
		);

		/**
		 * @var array perf-cheat for renderers
		 */
		protected $_renderers = [];

		public function getPaginatedIndexPageId($tid, $lastAction) {
			$indexPage = '/entries/index';

			if ($lastAction !== 'add'):
				if ($this->Session->read('paginator.lastPage')):
					$indexPage .= '/page:' . $this->Session->read('paginator.lastPage');
				endif;
			endif;
			$indexPage .= '/jump:' . $tid;

			return $indexPage;
		}

		public function getFastLink($entry, $params = array('class' => '')) {
			$out = "<a href='{$this->request->webroot}entries/view/{$entry['Entry']['id']}' class='{$params['class']}'>" .
					$this->getSubject($entry) . '</a>';
			return $out;
		}

		public function categorySelect($entry, $categories) {
			if ($entry['Entry']['pid'] == 0) {
				$out = $this->Form->input(
						'category',
						[
								'options' => [$categories],
								'empty' => true,
								'label' => __('Category'),
								'tabindex' => 1,
								'error' => ['notEmpty' => __('error_category_empty')]
						]
				);
			} else {
				// Send category for easy access in entries/preview when answering
				// (not used when saved).
				$out = $this->Form->hidden('category');
			}
			return $out;
		}

		/**
		 * renders a posting tree as thread
		 *
		 * @param mixed $tree passed as reference to share CU-decorator "up"
		 * @param $CurrentUser
		 * @param $options
		 * 	- 'renderer' [thread]|mix
		 * @return string
		 */
		public function renderThread(&$tree, ForumsUserInterface $CurrentUser, array $options = []) {
			$options += [
				'lineCache' => $this->_View->get('LineCache'),
				'maxThreadDepthIndent' => (int)Configure::read('Saito.Settings.thread_depth_indent'),
				'renderer' => 'thread'
			];
			$renderer = $options['renderer'];
			unset($options['renderer']);

			if (is_array($tree)) {
				$tree = $this->createTreeObject($tree, $options);
			}

			App::uses('PostingCurrentUserDecorator', 'Lib/Thread');
			$tree = $tree->addDecorator(function ($node) use ($CurrentUser) {
				$node = new PostingCurrentUserDecorator($node);
				$node->setCurrentUser($CurrentUser);
				return $node;
			});

			if (isset($this->_renderers[$renderer])) {
				$renderer = $this->_renderers[$renderer];
			} else {
				$name = $renderer;
				switch ($name) {
					case 'mix':
						App::uses('MixHtmlRenderer', 'Lib/Thread/Renderer');
						$renderer = new MixHtmlRenderer($this);
						break;
					default:
						App::uses('ThreadHtmlRenderer', 'Lib/Thread/Renderer');
						$renderer = new ThreadHtmlRenderer($this);
				}
				$this->_renderers[$name] = $renderer;
			}
			$renderer->setOptions($options);
			return $renderer->render($tree);
		}

		/**
		 * helper function for creating object thread tree
		 *
		 * @param array $entrySub
		 * @param array $options
		 * @return Posting
		 */
		public function createTreeObject(array $entrySub, array $options = []) {
			App::uses('Posting', 'Lib/Thread');
			return new Posting($entrySub, $options);
		}

	}
