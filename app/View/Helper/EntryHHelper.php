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
		protected $_catL10n = array();

		public function bookmarkLink($id, $isBookmarked = false) {
			$out = '';
			$_bookmarkLinkSet = $this->Html->link(
						'<i id="bookmarks-add-icon-' . $id . '" class="icon-bookmark icon-large"></i>',
						'/bookmarks/index/#' . $id,
						array(
							'id' => 'bookmarks-add-' . $id,
							'class' => 'btn-bookmark-add',
							'title' => __('Entry is bookmarked'),
							'escape' => false,
						)
				);

			if ($isBookmarked) {
				$out .= $_bookmarkLinkSet;
			} else {
				$out .= $this->Html->link(
						'<i id="bookmarks-add-icon-' . $id . '" class="icon-bookmark-empty icon-large"></i>', '#',
						array(
							'id' => 'bookmarks-add-' . $id,
							'class' => 'btn-bookmark-add',
							'title' => __('Bookmark this entry'),
							'escape' => false,
						)
				);
				$out .= $this->Html->scriptBlock(<<<EOF
$(document).ready(function (){
$("#content").one("click", "#bookmarks-add-{$id}", function (event) {
	$.ajax({
		async:true,
		data:"id={$id}",
		dataType:"json",
		success:function (data, textStatus) {
			$("#bookmarks-add-{$id}").replaceWith('{$_bookmarkLinkSet}');
			},
		type:"POST",
		url:"{$this->webroot}bookmarks/add"
	});
	return false;});
});
EOF
				);
			}
			return $out;
		}

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
			return isset($user['last_refresh'])
			&& strtotime($user['last_refresh']) < strtotime($entry['Entry']['time']);
		}

		public function isRoot($entry) {
			return (int)$entry['Entry']['pid'] === 0;
		}

		public function hasAnswers($entry) {
			return strtotime($entry['Entry']['last_answer']) > strtotime($entry['Entry']['time']);
		}

		public function markSolvedLink($entry, $rootEntry, $CurrentUser) {
			$out = '';
			// no button on root entry
			if ($this->isRoot($entry)) {
				return $out;
			}
			// only thread creator can mark as solved
			if ($CurrentUser->getId() !== (int)$rootEntry['Entry']['user_id']) {
				return $out;
			}

			$out = $this->Html->link(
				'<i class="icon-badge-solves icon-large"></i>',
				'#',
				array(
					'class' => 'btn-solves' .
							($entry['Entry']['solves'] ? ' solves-isSolved ' : ''),
					'data-id' => $entry['Entry']['id'],
					'title' => __('Mark entry as helpful'),
					'escape' => false,
				)
			);
			return $out;
		}

/**
 * @param $entry
 * @param $user
 * @return bool
 * @throws InvalidArgumentException
 */
		public function hasNewEntries($entry, $user) {
			if ($entry['Entry']['pid'] != 0):
				throw new InvalidArgumentException('Entry is no thread-root, pid != 0');
			endif;

			return strtotime($user['last_refresh']) < strtotime($entry['Entry']['last_answer']);
		}

		public function generateEntryTypeCss($level, $new, $current, $viewed) {
			$entryType = ($level === 0) ? 'thread' : 'reply';
			if ($new) {
				$entryType .= 'new';
			}
			if (!empty($viewed)) {
				if ($current === $viewed) {
					$entryType = ($level === 0) ? 'actthread' : 'actreply';
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
 * This function may be called serveral hundred times on the front page.
 * Don't make ist slow, benchmark!
 *
 * @param $entry
 * @param array $params
 * @return string
 */
		public function getFastLink($entry, $params = array('class' => '')) {
			// Stopwatch::start('Helper->EntryH->getFastLink()');
			$out = "<a href='{$this->request->webroot}entries/view/{$entry['Entry']['id']}' class='{$params['class']}'>{$entry['Entry']['subject']}" . (empty($entry['Entry']['text']) ? ' n/t' : '') . '</a>';
			// Stopwatch::stop('Helper->EntryH->getFastLink()');
			return $out;
		}

		public function getSubject($entry) {
			return $entry['Entry']['subject'] . (empty($entry['Entry']['text']) ? ' n/t' : '');
		}

		public function getBadges($entry) {
			$out = '';
			if ($entry['Entry']['fixed']) :
				$out .= '<i class="icon-pushpin" title="' . __('fixed') . '"></i> ';
			endif;
			if ($entry['Entry']['nsfw']):
				$out .= '<span class="sprite-nbs-explicit" title="' . __('entry_nsfw_title') . '"></span> ';
			endif;
			$out .= '<span class="solves ' . $entry['Entry']['id'] . '">';
			if ($entry['Entry']['solves']) {
				$out .= '<i class="icon-badge-solves solves-isSolved" title="' .
						__('Helpful entry') . '"></i>';
			}
			$out .= '</span>';
			return $out;
		}

		public function getCategorySelectForEntry($categories, $entry) {
			if ( $entry['Entry']['pid'] == 0 ):
				$out = $this->Form->input(
						'category',
						array(
						'options' => array( $categories ),
						'empty' => '',
						'label' => __('cateogry') . ':',
						'tabindex' => 1,
						'error' => array(
								'notEmpty' => __('error_category_empty'),
						),
						)
				);
			else :
				// Send category for easy access in entries/preview when anwsering.
				// If an entry is actually saved this value is not used but is looked up in DB.
				$out = $this->Form->hidden('category');
			endif;

			return $out;
		}

		/**
		 *
		 *
		 * Everything you do in here is in worst case done a few hundred times on
		 * the frontpage. Think about (and benchmark) performance before you change it.
		 */
		public function threadCached(array $entrySub, SaitoUser $CurrentUser, $level = 0, array $currentEntry = []) {
			// Stopwatch::start('EntryH->threadCached');
			//setup for current entry
			$_isNew = $this->isNewEntry($entrySub, $CurrentUser);
			$_currentlyViewed = (isset($currentEntry['Entry']['id']) &&
					$this->request->params['action'] === 'view') ? $currentEntry['Entry']['id'] : null;
			$_spanPostType = $this->generateEntryTypeCss($level,
				$_isNew,
				$entrySub['Entry']['id'],
				$_currentlyViewed);

			$_threadLineCached = $this->threadLineCached($entrySub, $level);

			if ($level === 0 &&
					strtotime($entrySub['Entry']['last_answer']) > strtotime($CurrentUser['last_refresh'])
			) {
				$_threadLinePre = '<i class="icon-threadnew"></i>';
			} else {
				$_threadLinePre = '<i class="icon-thread"></i>';
			}

			// generate current entry
			$out = <<<EOF
<li class="js-thread_line {$_spanPostType}" data-id="{$entrySub['Entry']['id']}" data-tid="{$entrySub['Entry']['tid']}" data-new="{$_isNew}">
	<div class="js-thread_line-content tl-cnt">
		<a href="#" class="btn_show_thread thread_line-pre span_post_type">
			{$_threadLinePre}
		</a>
		<a href='{$this->request->webroot}entries/view/{$entrySub['Entry']['id']}'
			class='link_show_thread {$entrySub['Entry']['id']} span_post_type thread_line-content'>
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
			// Stopwatch::stop('EntryH->threadCached');
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
				$class = '';
				$data = '';
				if ($level === 0) {
					$class = 'class="root"';
					$data = 'data-id="' . $id . '"';
				}
				$string = "<ul {$data} {$class}>" . $string . '</ul>';
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
			/* because of performance we use dont use $this->Html->link(...):
			 * $out.= $this->EntryH->getFastLink($entrySub,
			 *     array( 'class' => "link_show_thread {$entrySub['Entry']['id']} span_post_type" ));
			 */

			/*because of performance we use hard coded links instead the cakephp helper:
			 * echo $this->Html->link($entrySub['User']['username'], '/users/view/'. $entrySub['User']['id']);
			 */
			$category = '';
			if ($level === 0) :
				if (!isset($this->_catL10n[$entrySub['Category']['accession']])) {
					$this->_catL10n[$entrySub['Category']['accession']] = __d('nondynamic',
						'category_acs_' . $entrySub['Category']['accession'] . '_exp');
				}
				$a = $this->_catL10n[$entrySub['Category']['accession']];
				$category = '<span class="category_acs_' . $entrySub['Category']['accession'] . '"
            title="' . $entrySub['Category']['description'] . ' ' . ($a) . '">
        (' . $entrySub['Category']['category'] . ')
      </span>';
			endif;

			// normal time output
			$time = $this->TimeH->formatTime($entrySub['Entry']['time']);

			$subject = $this->getSubject($entrySub);
			$badges = $this->getBadges($entrySub);

			// wrap everything up
			$out = <<<EOF
{$subject}
<span class="thread_line-username"> – {$entrySub['User']['username']}</span>
{$category}
<span class="thread_line-post"> {$time} {$badges} </span>
EOF;
			return $out;
		}

	}
