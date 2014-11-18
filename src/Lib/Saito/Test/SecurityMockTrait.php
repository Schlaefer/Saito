<?php

	namespace Saito\Test;

	use Cake\Controller\Controller;
	use Cake\Event\Event;
	use Cake\Event\EventManager;

	trait SecurityMockTrait {

		public function mockSecurity() {
            // @todo 3.0 swtich to on()
			EventManager::instance()->attach(
				[$this, 'securityByPass'],
				'Controller.initialize'
			);
		}

		/**
		 * Assume that SecurityComponent was called
		 *
		 * @param Event $event
		 */
		public function securityBypass(Event $event) {
			/** @var Controller $Controller */
			$Controller = $event->subject();

			$Security = $this->getMock(
				'\Cake\Controller\Component\SecurityComponent',
				['_validatePost'],
				[$Controller->components()]
			);

			$Security
                // @todo 3.0 @bogus throws error
//                ->expects($this->atLeastOnce())
                ->expects($this->any())
				->method('_validatePost')
				->will($this->returnValue(true));

			$Controller->components()->set('Security', $Security);
		}

	}
