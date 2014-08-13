<?php

  namespace Phile\Plugin\Siezi\Img;

  class Plugin extends \Phile\Plugin\AbstractPlugin implements \Phile\Gateway\EventObserverInterface {

		public function __construct() {
			\Phile\Event::registerEvent('before_parse_content', $this);
		}

		public function on($eventKey, $data = null) {
			if ($eventKey === 'before_parse_content') {
				$this->_work($data);
			}
		}

		protected function _work(&$data) {
			$data['page']->setContent(
				preg_replace('/img:(.*)(\s|$)/',
					"<div class='img' style=\"text-align: center;\">
							<img  style=\" margin: 0 -100% 0 -100%;\" src=\"content/img/$1\" alt=\"image\">
					</div>", $data['content'])
			);
		}

	}
