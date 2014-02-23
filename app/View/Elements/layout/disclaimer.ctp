<div class="l-disclaimer bp-threeColumn">
  <div class="left">
    <div class="disclaimer-inside">
      <h3><?= __('Ressources') ?></h3>
      <ul>
        <li>
          <a href="<?= $this->request->webroot ?>users/contact/0"><?= __('Contact') ?></a>
        </li>
				<li>
					<a href="<?= $this->request->webroot ?>mobile/"><?= __('Mobile') ?></a>
				</li>
        <li>
          <a href="<?= $this->request->webroot ?>pages/rss_feeds"><?= __('RSS') ?></a>
        </li>
      </ul>
    </div>
  </div>
  <div class="center">
    <div class="disclaimer-inside">
      <h3><?= __('Status') ?></h3>
			<p>
				<?php
					$loggedin = $HeaderCounter['user_registered'];
					if ($CurrentUser->isLoggedIn()) {
						$loggedin = $this->Html->link($loggedin, '/users/index');
					}
					echo String::insert(
							__(':entries Entries in :threads Threads; :registered members, :loggedin logged in, :anon anonymous'),
							[
									'entries' => number_format($HeaderCounter['entries'], null, null, '.'),
									'threads' => number_format($HeaderCounter['threads'], null, null, '.'),
									'registered' => number_format($HeaderCounter['user'], null, null, '.'),
									'loggedin' => $loggedin,
									'anon' => $HeaderCounter['user_anonymous']
							]);
				?>
			</p>
			<p>
				<?php
					$_user = $HeaderCounter['latestUser']['User'];
					$_u = $_user['username'];
					if ($CurrentUser->isLoggedIn()) {
						$_u = $this->Html->link($_user['username'],
								'/users/view/' . $_user['id']);
					}
					echo __('disclaimer.newestMember', $_u);
				?>
			</p>
    </div>
  </div>
  <div class="right">
    <div class="disclaimer-inside">
      <h3><?= __('About') ?></h3>
      <p>
        <a href="<?= Configure::read('Saito.saitoHomepage') ?>">
					<?= __('Powered by Saito v%s.', Configure::read("Saito.v")) ?>
				</a>
        <br/>
        <?= __('Generated in %s s.', Stopwatch::getWallTime()) ?>
      </p>
    </div>
  </div>
</div>