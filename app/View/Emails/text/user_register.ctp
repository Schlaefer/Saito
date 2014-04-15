<?php
	echo __('register_email_content', [
			Configure::read('Saito.Settings.forum_name'),
			$this->Html->url(
				[
					'controller' => 'users',
					'action' => 'register',
					$user['User']['id'],
					$user['User']['activate_code']
				],
				true)
		]
	);
	echo $this->element('email/text/footer');