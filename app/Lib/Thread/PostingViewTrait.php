<?php

	/**
	 * Helper methods for rendering postings
	 */
	trait PostingViewTrait {

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

	}