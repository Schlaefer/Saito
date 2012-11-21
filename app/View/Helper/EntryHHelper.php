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
		 *
		 *
		 * Perf-cheat
		 *
		 * @var array
		 */
		protected $_catL10n = array();

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
			$isNewEntry = FALSE;
			if (strtotime($user['last_refresh']) < strtotime($entry['Entry']['time'])):
				$isNewEntry = TRUE;
			endif;
			return $isNewEntry;
		}

		public function hasNewEntries($entry, $user) {
			if ($entry['Entry']['pid'] != 0):
				throw new InvalidArgumentException("Entry is no thread-root, pid != 0");
			endif;

			return strtotime($user['last_refresh']) < strtotime($entry['Entry']['last_answer']);
		}

		public function generateThreadParams($params) {

			extract($params);

			$is_new_post = false;
			if ( $level == 0 ) {
				if ( isset($last_refresh)
						&& strtotime($last_refresh) < strtotime($entry_time)
				) {
					$span_post_type = 'threadnew';
					$is_new_post = true;
				} else {
					$span_post_type = 'thread';
				}
			} else {
				if ( isset($last_refresh)
						&& strtotime($last_refresh) < strtotime($entry_time)
				) {
					$span_post_type = 'replynew';
					$is_new_post = true;
				} else {
					$span_post_type = 'reply';
				}
			}

			### determine act_thread  start ###
			$act_thread = false;
			if ( $this->request->params['action'] == 'view' ) {
				if ( $level == 0 ) {
					if ( $entry_current == $entry_viewed ) {
						$span_post_type = 'actthread';
						$act_thread = true;
					}
				} else {
					if ( $entry_current == $entry_viewed ) {
						$span_post_type = 'actreply';
						$act_thread = true;
					}
				}
			}
			### determine act_thread end ###

			$out = array(
					'act_thread',
					'is_new_post',
					'span_post_type',
			);

			return compact($out);
		}

    public function getPaginatedIndexPageId($tid, $lastAction) {
      $indexPage = '/entries/index';

      if ( $lastAction !== 'add' ):
        if ( $this->Session->read('paginator.lastPage') ):
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
		 * @param type $entry
		 * @param type $params
		 * @return string
		 */
		public function getFastLink($entry, $params = array( 'class' => '' )) {
//		Stopwatch::start('Helper->EntryH->getFastLink()');
			$out = "<a href='{$this->request->webroot}entries/view/{$entry['Entry']['id']}' class='{$params['class']}'>{$entry['Entry']['subject']}" . (empty($entry['Entry']['text']) ? ' n/t' : '') . "</a>";
//		Stopwatch::stop('Helper->EntryH->getFastLink()');
			return $out;
		}

		public function getSubject($entry) {
			return $entry['Entry']['subject'] . (empty($entry['Entry']['text']) ? ' n/t' : '');
		}

		public function getBadges($entry) {
			$out = array();
			if ($entry['Entry']['fixed']) :
				$out[] = '<i class="icon-pushpin" title="' . __('fixed') . '"></i>';
			endif;
			if ($entry['Entry']['nsfw']):
				$out[] = '<span class="sprite-nbs-explicit" title="' . __('entry_nsfw_title') . '"></span>';
			endif;
			return implode(' ', $out);
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
		public function threadCached(array $entry_sub, SaitoUser $CurrentUser, $level = 0, array $current_entry = array()) {
			// Stopwatch::start('EntryH->threadCached');
			//setup for current entry
			$params = $this->generateThreadParams(
					array(
							'level'					 => $level,
							'last_refresh'	 => $CurrentUser['last_refresh'],
							'entry_time'		 => $entry_sub['Entry']['time'],
							// @td $entry['Entry']['id'] not set in user/view.ctp
							'entry_viewed'	 => (isset($current_entry['Entry']['id']) && $this->request->params['action'] == 'view') ? $current_entry['Entry']['id'] : null,
							'entry_current'	 => $entry_sub['Entry']['id'],
					)
			);
			extract($params);

			$new_post_class = (($is_new_post) ? " new" : '');
			$thread_line_cached = $this->threadLineCached($entry_sub, $level);

			// generate current entry
			$out = '';

			$out .= <<<EOF
<li class="js-thread_line {$span_post_type}" data-id="{$entry_sub['Entry']['id']}" data-tid="{$entry_sub['Entry']['tid']}" data-new="{$is_new_post}">
	<div class="js-thread_line-content {$entry_sub['Entry']['id']} thread_line {$new_post_class}" data-id="{$entry_sub['Entry']['id']}" style='position: relative;'>
		<div class="thread_line-pre">
			<a href="#" class="btn_show_thread {$entry_sub['Entry']['id']} span_post_type">
				&bull;
			</a>
		</div>
		<a href='{$this->request->webroot}entries/view/{$entry_sub['Entry']['id']}'
			class='link_show_thread {$entry_sub['Entry']['id']} span_post_type thread_line-content'>
				{$thread_line_cached}
		</a>
	</div>
</li>
EOF;


			// generate sub-entries of current entry
			if (isset($entry_sub['_children'])) :
				$out .= '<li>';
				foreach ($entry_sub['_children'] as $child) :
					$out .= $this->threadCached($child, $CurrentUser, $level + 1, $current_entry);
				endforeach;
				$out .= '</li>';
			endif;

			// wrap everything up
			if ($level < $this->_maxThreadDepthIndent) {
				$wrapper_start = '<ul id="ul_thread_' . $entry_sub['Entry']['id'] . '" class="' . (($level === 0) ? 'thread' : 'reply') . '">';
				$wrapper_end	 = '</ul>';
				$out					 = $wrapper_start . $out . $wrapper_end;
			}

			// Stopwatch::stop('EntryH->threadCached');
			return $out;
		}

		/**
		 *
		 *
		 * Everything you do in here is in worst case done a few hundred times on
		 * the frontpage. Think about (and benchmark) performance before you change it.
		 */
		public function threadLineCached(array $entry_sub, $level) {
			/* because of performance we use dont use $this->Html->link(...):
			 * $out.= $this->EntryH->getFastLink($entry_sub,
			 *     array( 'class' => "link_show_thread {$entry_sub['Entry']['id']} span_post_type" ));
			 */

			/*because of performance we use hard coded links instead the cakephp helper:
			 * echo $this->Html->link($entry_sub['User']['username'], '/users/view/'. $entry_sub['User']['id']);
			 */
			$category = '';
			if ($level === 0) :
				if (!isset($this->_catL10n[$entry_sub['Category']['accession']])) {
					$this->_catL10n[$entry_sub['Category']['accession']] = __d('nondynamic',
						'category_acs_' . $entry_sub['Category']['accession'] . '_exp');
				} 				
				$a = $this->_catL10n[$entry_sub['Category']['accession']];
				$category = '<span class="category_acs_' . $entry_sub['Category']['accession'] . '"
            title="' . $entry_sub['Category']['description'] . ' ' . ($a) . '">
        (' . $entry_sub['Category']['category'] . ')
      </span>';
			endif;

			// normal time output
			$time = $this->TimeH->formatTime($entry_sub['Entry']['time']);

			// the schlaefer awe-some-o macnemo shipbell time output
			/* <span title="<?php echo $this->TimeH->formatTime($entry_sub['Entry']['time']); ?>"><?php echo $this->TimeH->formatTime($entry_sub['Entry']['time'], 'glasen'); ?>
			  </span> */

			$subject = $this->getSubject($entry_sub);
			$badges = $this->getBadges($entry_sub);

			// wrap everything up
			$out = <<<EOF
{$subject}
<span class="mobile-nl">
  <span class="thread_line-username">
    <span class="mobile-hide"> – </span>
		{$entry_sub['User']['username']}
	</span>
	{$category}
	<span class="thread_line-post">
	  {$time} {$badges}
  </span>
</span>
EOF;
			return $out;
		}

	}
