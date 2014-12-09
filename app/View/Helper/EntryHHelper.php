<?php

	use Saito\Thread\Renderer;

	App::uses('AppHelper', 'View/Helper');


	# @td refactor helper name to 'EntryHelper'
	/**
	 * @package saito_entry
	 */

	class EntryHHelper extends AppHelper {

		use \Saito\Posting\Renderer\HelperTrait;

		public $helpers = ['Form', 'Html', 'Session', 'TimeH'];

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
			// @todo @performance
			$out = "<a href='{$this->request->webroot}entries/view/{$entry['Entry']['id']}' class='{$params['class']}'>" .
					$this->getSubject($this->dic->newInstance('\Saito\Posting\Posting', ['rawData' => $entry])) . '</a>';
			return $out;
		}

		public function categorySelect($entry, $categories) {
			if ($entry['Entry']['pid'] == 0) {
				$out = $this->Form->input(
						'category_id',
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
				$out = $this->Form->hidden('category_id');
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
		public function renderThread(&$tree, array $options = []) {
			$options += [
				'lineCache' => $this->_View->get('LineCache'),
				'maxThreadDepthIndent' => (int)Configure::read('Saito.Settings.thread_depth_indent'),
				'renderer' => 'thread',
				'rootWrap' => false
			];
			$renderer = $options['renderer'];
			unset($options['renderer']);

			if (is_array($tree)) {
				$tree = $this->createTreeObject($tree, $options);
			}

			if (isset($this->_renderers[$renderer])) {
				$renderer = $this->_renderers[$renderer];
			} else {
				$name = $renderer;
				switch ($name) {
					case 'mix':
						$renderer = new Renderer\MixHtmlRenderer($this);
						break;
					default:
						$renderer = new Renderer\ThreadHtmlRenderer($this);
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
			$tree = $this->dic->newInstance(
				'\Saito\Posting\Posting',
				['rawData' => $entrySub, 'options' => $options]
			);
			return $tree;
		}

	}
