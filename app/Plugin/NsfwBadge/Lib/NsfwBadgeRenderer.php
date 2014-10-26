<?php

	App::uses('SaitoEventListener', 'Lib/Saito/Event');

	/**
	 * Class NsfwBadgeRenderer
	 *
	 * @module NsfwBadge
	 * @package NsfwBadge
	 */
	class NsfwBadgeRenderer implements SaitoEventListener {

		protected $_badge;

		protected $_Controller;

		const CSS = '.posting-badge-nsfw { color: #FFF; background-color: #F00; }';

		public function implementedSaitoEvents() {
			return [
				'Event.Saito.Model.initialize' => 'initModels',
				'Event.Saito.View.beforeRender' => 'viewBeforeRender',
				'Request.Saito.View.Posting.addForm' => 'postingAdd',
				'Request.Saito.View.Posting.badges' => 'nsfwBadge'
			];
		}

		public function viewBeforeRender($eventData) {
			$View = $eventData['subject'];
			$View->append('css', '<style>' . self::CSS . '</style>');

			// we assume that an answers to a nsfw posting isn't nsfw itself
			if ($View->request->action === 'add' && $View->request->controller === 'entries') {
					unset($View->request->data['Entry']['nsfw']);
			}
		}

		public function initModels($eventData) {
			$Model = $eventData['Model'];
			if ($Model->alias !== 'Entry') {
				return;
			}

			$key = 'Entry.nsfw';
			$Model->threadLineFieldList[$key] = $key;

			//= allows flattr field in Entry::create() and Entry::update()
			$Model->allowedInputFields['create'][] = 'nsfw';
			$Model->allowedInputFields['update'][] = 'nsfw';
		}

		public function nsfwBadge($eventData) {
			if (!$eventData['posting']['Entry']['nsfw']) {
				return;
			}
			if (!$this->_badge) {
				$this->_badge = '<span class="posting-badge posting-badge-nsfw" title="' .
					__d('nsfw_badge', 'nsfw.exp') . '">' . __d('nsfw_badge',
						'nsfw.title') .
					'</span> ';
			}
			return $this->_badge;
		}

		public function postingAdd($eventData) {
			$View = $eventData['View'];
			$checkbox = $View->Form->checkbox('nsfw');
			$checkbox .= $View->Form->label('nsfw', __d('nsfw_badge', 'nsfw.exp'));
			return $View->Html->tag('div', $checkbox,
				['div' => ['class' => 'checkbox']]);
		}

	}