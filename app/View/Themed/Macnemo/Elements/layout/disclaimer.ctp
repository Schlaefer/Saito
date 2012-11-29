<div class="l-disclaimer bp-threeColumn">
	<div class="left">
    <div class="disclaimer-inside">
			<h3><?php echo __('Ressources'); ?> </h3>
			<ul>
				<li>
					<a href="http://macnemo.de/wiki/">Wiki</a>
				</li>
				<li>
					<a href="<?php echo $this->request->webroot ?>users/contact/0"><?php echo __('Contact') ?></a>
				</li>
				<li>
					<a href="aim:gochat?roomname=macnemo">Plauderecke</a>
				</li>
				<li>
					<a href="http://macnemo.de/wiki/index.php/Main/Impressum">Impressum</a>
				</li>
				<li>
          <a href="<?php echo $this->request->webroot ?>pages/rss_feeds"><?php echo __('RSS') ?></a>
				</li>
				<li>
          <a href="http://www.google.com/moderator/#15/e=d490b&amp;t=d490b.40">Feedback geben</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="center">
    <div class="disclaimer-inside">
      <h3><?php echo __('Status'); ?></h3>
      <?php
				$loggedin = $HeaderCounter['user_registered'];
				if ($CurrentUser->isLoggedIn()) {
					$loggedin = $this->Html->link($loggedin, '/users/index');
				}
				echo String::insert(
						__(':entries mal Seemansgarn in :threads unglaublichen Geschichten; :registred geheuert, :loggedin an Deck, :anon Blinde Passagiere'),
						array(
								'entries' => number_format($HeaderCounter['entries'], null, null, '.'),
								'threads' => number_format($HeaderCounter['threads'], null, null, '.'),
								'registred' => number_format($HeaderCounter['user'], null, null, '.'),
								'loggedin' => $loggedin,
								'anon' => $HeaderCounter['user_anonymous']
						)
					);
      ?>
		</div>
	</div>
	<div class="right">
    <div class="disclaimer-inside">
			<h3> Maschinenraum </h3>
			<p>
        <a href="http://saito.siezi.com/"><?php echo __('Powered by Saito v%s.', Configure::read("Saito.v")); ?></a>
        <br/>
        <?php echo __('Generated in %s s.', Stopwatch::getWallTime()); ?>
			</p>
		</div>

	</div>
</div>