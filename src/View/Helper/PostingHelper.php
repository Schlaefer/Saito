<?php

	namespace App\View\Helper;

	use Cake\Core\Configure;
	use Cake\ORM\Entity;
	use Saito\App\Registry;
	use Saito\Posting\Posting;
	use Saito\Thread\Renderer;

	class PostingHelper extends AppHelper {

		use \Saito\Posting\Renderer\HelperTrait;

		public $helpers = ['Form', 'Html', 'TimeH'];

		/**
		 * @var array perf-cheat for renderers
		 */
		protected $_renderers = [];

		public function getPaginatedIndexPageId($tid, $lastAction) {
			$indexPage = '/entries/index';

			if ($lastAction !== 'add') {
                $session = $this->request->session();
                if ($session->read('paginator.lastPage')) {
                    $indexPage .= '/page:' . $session->read('paginator.lastPage');
                }
            }
			$indexPage .= '/jump:' . $tid;

			return $indexPage;
		}

		public function getFastLink(array $entry, $options = ['class' => '']) {
			// @todo @performance remove conversion(?)
			$posting = Registry::newInstance(
				'\Saito\Posting\Posting', ['rawData' => $entry]
			);
			$id = $posting->get('id');
			$out = "<a href='{$this->request->webroot}entries/view/{$id}' class='{$options['class']}'>" . $this->getSubject($posting) . '</a>';
			return $out;
		}

		/**
		 * @param $posting
		 * @param $categories
		 * @return string
		 */
		public function categorySelect(Entity $posting, array $categories) {
			if ($posting->isRoot()) {
				$html = $this->Form->input(
						'category_id',
						[
								'options' => $categories,
								'empty' => true,
								'label' => __('Category'),
								'tabindex' => 1,
								'error' => ['notEmpty' => __('error_category_empty')]
						]
				);
			} else {
				// Send category for easy access in entries/preview when answering
				// (not used when saved).
				$html = $this->Form->hidden('category_id');
			}
			return $html;
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
		public function renderThread(Posting $tree, array $options = []) {
			$options += [
				'lineCache' => $this->_View->get('LineCache'),
				'maxThreadDepthIndent' => (int)Configure::read('Saito.Settings.thread_depth_indent'),
				'renderer' => 'thread',
				'rootWrap' => false
			];
			$renderer = $options['renderer'];
			unset($options['renderer']);

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

	}
