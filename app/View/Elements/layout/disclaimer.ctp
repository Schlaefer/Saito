<?php Stopwatch::start('layout/disclaimer.ctp'); ?>
<div class="l-disclaimer bp-threeColumn">
  <div class="left">
    <div class="disclaimer-inside">
      <h3><?= __('Ressources') ?></h3>
      <ul>
        <li>
          <a href="<?= $this->request->webroot ?>contacts/owner"><?= __('Contact') ?></a>
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
					echo CakeText::insert( __('discl.status'), [
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
					$_u = $this->Layout->linkToUserProfile($_user, $CurrentUser);
					echo __('discl.newestMember', $_u);
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
        <?php
          $time = Stopwatch::getWallTime(Configure::read('Config.language'));
          echo __('Generated in %s s.', $time);
        ?>
      </p>
    </div>
  </div>
</div>
<?php Stopwatch::stop('layout/disclaimer.ctp'); ?>
