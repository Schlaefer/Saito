<?php
	echo __('register_email_content', [
            $forumName,
			$this->Url->build(
				[
					'controller' => 'users',
					'action' => 'rs',
					$user->get('id'),
					'?' => ['c' => $user->get('activate_code')]
				],
				true)
		]
	);
	echo $this->element('email/text/footer');
