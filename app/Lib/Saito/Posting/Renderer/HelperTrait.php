<?php

	namespace Saito\Posting\Renderer;

	\App::uses('SaitoEventManager', 'Lib/Saito/Event');

	/**
	 * Helper methods for rendering postings
	 */
	trait HelperTrait {

		/** * @var SaitoEventManager */
		protected $_SEM;

		public function getBadges($entry) {
			$out = '';
			if ($entry['Entry']['fixed']) {
				$out .= '<i class="fa fa-thumb-tack" title="' . __('fixed') . '"></i> ';
			}
			// anchor for inserting solve-icon via FE-JS
			$out .= '<span class="solves ' . $entry['Entry']['id'] . '">';
			if ($entry['Entry']['solves']) {
				$out .= $this->solvedBadge();
			}
			$out .= '</span>';

			if (!isset($this->_SEM)) {
				$this->_SEM = \SaitoEventManager::getInstance();
			}
			$additionalBadges = $this->_SEM->dispatch(
				'Request.Saito.View.Posting.badges',
				['posting' => $entry]
			);
			if ($additionalBadges) {
				$out .= implode('', $additionalBadges);
			}

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
			return \h($entry['Entry']['subject']) . (empty($entry['Entry']['text']) ? ' n/t' : '');
		}

	}