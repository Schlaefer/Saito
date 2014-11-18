<p>
	<?php
		use Cake\Utility\String;

		$loggedin = $HeaderCounter['user_registered'];
		if ($CurrentUser->isLoggedIn()) {
			$loggedin = $this->Html->link($loggedin, '/users/index');
		}
		echo String::insert(
			__('discl.status'), [
				'entries' => number_format($HeaderCounter['entries'], null, null, '.'),
				'threads' => number_format($HeaderCounter['threads'], null, null, '.'),
				'registered' => number_format($HeaderCounter['user'], null, null, '.'),
				'loggedin' => $loggedin,
				'anon' => $HeaderCounter['user_anonymous']
			]
		);

	?>
</p>
<p>
	<?php
		$_user = $HeaderCounter['latestUser'];
		$_u = $this->UserH->linkToUserProfile($_user, $CurrentUser);
		echo __('discl.newestMember', $_u);
	?>
</p>
