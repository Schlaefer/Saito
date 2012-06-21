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
		);

		public function generateThreadParams($params) {

			extract($params);
//		debug($last_refresh);
//		debug($entry_time);

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

	}

?>