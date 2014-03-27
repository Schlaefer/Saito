<?php
	App::uses('AppHelper', 'View/Helper');

	# @td refactor helper name to 'EntryHelper'
	/**
	 * @package saito_entry
	 */

	class EntryHHelper extends AppHelper {

		public $helpers = array(
				'Form',
				'Html',
				'Session',
				'TimeH',
		);

/**
 *
 * Perf-cheat
 *
 * @var int
 */
		protected $_maxThreadDepthIndent;

/**
 * Category localization
 *
 * Perf-cheat
 *
 * @var array
 */
		protected $_catL10n = [];

		public function beforeRender($viewFile) {
			parent::beforeRender($viewFile);
			$this->_maxThreadDepthIndent = (int)Configure::read('Saito.Settings.thread_depth_indent');
		}

/**
 * Decides if an $entry is new to/unseen by a $user
 *
 * @param type $entry
 * @param type $user
 * @return boolean
 */
		public function isNewEntry($entry, $user) {
			if (!isset($user['last_refresh'])) {
				return false;
			}
			$read = $user->ReadEntries->get();
			$_isNew = strtotime($user['last_refresh']) < strtotime($entry['Entry']['time']) &&
					!isset($read[$entry['Entry']['id']]);

			return $_isNew;
		}

		public function isRoot($entry) {
			return (int)$entry['Entry']['pid'] === 0;
		}

		public function hasAnswers($entry) {
			return strtotime($entry['Entry']['last_answer']) > strtotime($entry['Entry']['time']);
		}

		public function isPinned($entry) {
			return (bool)$entry['Entry']['fixed'];
		}

/**
 * @param $entry
 * @param $user
 * @return bool
 * @throws InvalidArgumentException
 */
		public function hasNewEntries($entry, $user) {
			if ($entry['Entry']['pid'] != 0) {
				throw new InvalidArgumentException('Entry is no thread-root, pid != 0');
			}
			return strtotime($user['last_refresh']) < strtotime($entry['Entry']['last_answer']);
		}

		public function generateEntryTypeCss($level, $new, $current, $viewed) {
			$entryType = ($level === 0) ? 'et-root' : 'et-reply';
			if ($new) {
				$entryType .= ' et-new';
			} else {
				$entryType .= ' et-old';
			}
			if (!empty($viewed)) {
				if ($current === $viewed) {
					$entryType .= ' et-current';
				}
			}
			return $entryType;
		}

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

		/**
		 * evaluates if entry is n/t
		 *
		 * @param $entry
		 * @return bool
		 */
		public function isNt($entry) {
			return empty($entry['Entry']['text']);
		}

		public function getFastLink($entry, $params = array('class' => '')) {
			$out = "<a href='{$this->request->webroot}entries/view/{$entry['Entry']['id']}' class='{$params['class']}'>" .
					$this->getSubject($entry) . '</a>';
			return $out;
		}

		public function solvedBadge() {
			return '<i class="fa fa-badge-solves solves-isSolved" title="' .
					__('Helpful entry') . '"></i>';
		}

		/**
		 *
		 * This function may be called serveral hundred times on the front page.
		 * Don't make ist slow, benchmark!
		 *
		 * @param $entry
		 * @return string
		 */
		public function getSubject($entry) {
			return h($entry['Entry']['subject']) . (empty($entry['Entry']['text']) ? ' n/t' : '');
		}

		public function getBadges($entry) {
			$out = '';
			if ($entry['Entry']['fixed']) {
				$out .= '<i class="fa fa-thumb-tack" title="' . __('fixed') . '"></i> ';
			}
			if ($entry['Entry']['nsfw']) {
				$out .= '<span class="posting-badge-nsfw" title="' .
						__('entry_nsfw_title') . '">' . __('posting.badge.nsfw') .
						'</span> ';
			}
			// anchor for inserting solve-icon via FE-JS
			$out .= '<span class="solves ' . $entry['Entry']['id'] . '">';
			if ($entry['Entry']['solves']) {
				$out .= $this->solvedBadge();
			}
			$out .= '</span>';
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
		 *
		 *
		 * Everything you do in here is in worst case done a few hundred times on
		 * the frontpage. Think about (and benchmark) performance before you change it.
		 */
		public function threadCached(array $entrySub, SaitoUser $CurrentUser, $level = 0, array $currentEntry = []) {
			//setup for current entry
			$_isNew = $this->isNewEntry($entrySub, $CurrentUser);
			$_currentlyViewed = (isset($currentEntry['Entry']['id']) &&
					$this->request->params['action'] === 'view') ? $currentEntry['Entry']['id'] : null;
			$_spanPostType = $this->generateEntryTypeCss($level,
				$_isNew,
				$entrySub['Entry']['id'],
				$_currentlyViewed);

			$_threadLineCached = $this->threadLineCached($entrySub, $level);

			// generate current entry
			$out = <<<EOF
<li class="threadLeaf {$_spanPostType}" data-id="{$entrySub['Entry']['id']}" data-tid="{$entrySub['Entry']['tid']}" data-new="{$_isNew}">
	<div class="threadLine">
		<button href="#" class="btnLink btn_show_thread threadLine-pre et">
			<i class="fa fa-thread"></i>
		</button>
		<a href='{$this->request->webroot}entries/view/{$entrySub['Entry']['id']}'
			class='link_show_thread {$entrySub['Entry']['id']} et threadLine-content'>
				{$_threadLineCached}
		</a>
	</div>
</li>
EOF;

			// generate sub-entries of current entry
			if (isset($entrySub['_children'])) {
				$sub = '';
				foreach ($entrySub['_children'] as $child) {
					$sub .= $this->threadCached($child, $CurrentUser, $level + 1, $currentEntry);
				}
				$out .= '<li>' . $this->_wrapUl($sub) . '</li>';
			}

			// wrap into root ul tag
			if ($level === 0) {
				$out = $this->_wrapUl($out, $level, $entrySub['Entry']['id']);
			}
			return $out;
		}

		/**
		 * Wraps li tags with ul tag
		 *
		 * @param $string li html list
		 * @param $level
		 * @param $id
		 * @return string
		 */
		protected function _wrapUl($string, $level = null, $id = null) {
			if ($level < $this->_maxThreadDepthIndent) {
				$class = 'threadTree-node';
				$data = '';
				if ($level === 0) {
					$class .= ' root';
					$data = 'data-id="' . $id . '"';
				}
				$string = "<ul {$data} class=\"{$class}\">{$string}</ul>";
			}
			return $string;
		}

/**
 *
 *
 * Everything you do in here is in worst case done a few hundred times on
 * the frontpage. Think about (and benchmark) performance before you change it.
 */
		public function threadLineCached(array $entrySub, $level) {
			$category = '';
			if ($level === 0) {
				if (!isset($this->_catL10n[$entrySub['Category']['accession']])) {
					$this->_catL10n[$entrySub['Category']['accession']] = __d('nondynamic',
						'category_acs_' . $entrySub['Category']['accession'] . '_exp');
				}
				$categoryTitle = $this->_catL10n[$entrySub['Category']['accession']];
				$category = '<span class="c-category acs-' . $entrySub['Category']['accession'] . '"
            title="' . $entrySub['Category']['description'] . ' ' . ($categoryTitle) . '">
        (' . $entrySub['Category']['category'] . ')
      </span>';
			}

			// normal time output
			$time = $this->TimeH->formatTime($entrySub['Entry']['time']);

			$subject = $this->getSubject($entrySub);
			$badges = $this->getBadges($entrySub);
			$username = h($entrySub['User']['username']);

			// wrap everything up
			$out = <<<EOF
{$subject}
<span class="c-username"> – {$username}</span>
{$category}
<span class="threadLine-post"> {$time} {$badges} </span>
EOF;
			return $out;
		}

	}
