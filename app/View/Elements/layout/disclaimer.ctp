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
      <?php
				$loggedin = $HeaderCounter['user_registered'];
				if ($CurrentUser->isLoggedIn()) {
					$loggedin = $this->Html->link($loggedin, '/users/index');
				}
				echo String::insert(
					__(
						':entries Entries in :threads Threads; :registred registred users, :loggedin logged in, :anon anonymous'
					),
					[
							'entries' => number_format($HeaderCounter['entries'], null, null, '.'),
							'threads' => number_format($HeaderCounter['threads'], null, null, '.'),
							'registred' => number_format($HeaderCounter['user'], null, null, '.'),
							'loggedin' => $loggedin,
							'anon' => $HeaderCounter['user_anonymous']
					]
				);
      ?>
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