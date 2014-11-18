<?php

	namespace App\Controller\Component;

	use Cake\Controller\Component;
	use Cake\Utility\String;
	use Saito\Posting\Posting;

	class TitleComponent extends Component {

		/**
		 *
		 * @param $posting
		 * @param null $type
		 */
		public function setFromPosting(Posting $posting, $type = null) {
			if ($type === null) {
				$template = __(':subject | :category');
			} else {
				$template = __(':subject (:type) | :category');
			}
			$this->_registry->getController()->set(
				'title_for_layout',
				String::insert(
					$template,
					[
						'category' => $posting->get('Category')['category'],
						'subject' => $posting->get('subject'),
						'type' => $type
					]
				)
			);
		}


	}
