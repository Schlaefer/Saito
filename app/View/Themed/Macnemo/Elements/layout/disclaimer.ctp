<div class="bp_four_column" style='width: 951px; margin: 0 auto; position: relative;'>
	<?php // @lo this .ctp ?>
	<div class="left">
		<div class="inside">
			<h3><?php echo __('Ressources'); ?> </h3>
			<ul>
				<li>
					<a href="http://macnemo.de/wiki/">Wiki</a>
				</li>
				<li>
					<a href="<?PHP echo $this->request->webroot ?>users/contact/1"><?php echo __('Contact') ?></a><!-- @lo  -->
				</li>
				<li>
					<a href="aim:gochat?roomname=macnemo">Plauderecke</a>
				</li>
				<li>
					<a href="http://macnemo.de/wiki/index.php/Main/Impressum">Impressum</a>
				</li>
				<li>
					<a href="<?php echo $this->request->webroot; ?>pages/rss_feeds">RSS</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="center_l">
		<div class="inside">
      <h3><?php echo __('Status'); ?></h3>
			<?php echo  number_format($HeaderCounter['entries'],
					null, null, '.') ?> mal Seemannsgarn in
			<?php echo  number_format($HeaderCounter['threads'],
					null, null, '.') ?> unglaublichen Geschichten;
			<?php echo  number_format($HeaderCounter['user'],
					null, null, '.') ?> geheuert,
			<?php echo  $HeaderCounter['user_registered'] ?> an Deck,
			<?php echo  $HeaderCounter['user_anonymous'] ?> Blinde Passagiere.
		</div>
	</div>
	<div class="center_r">
		<div class="inside">
			<h2>Unterstützen</h2>
			<p>Macnemo braucht Unterstützung. <a href="/wiki/index.php/Main/Unterst%c3%bctzen">Das Wie, Was, Wo und Wer findet sich im Wiki.</a></p>
		</div>
	</div>
	<div class="right">
		<div class="inside">
			<h3> Maschinenraum </h3>
			<p>
        <a href="http://saito.siezi.com/"><?php echo __('Powered by Saito  v%s.', Configure::read("Saito.v")); ?></a>
			</p>
			<p>
				<a href="http://www.google.com/moderator/#15/e=d490b&t=d490b.40">Feedback geben.</a>
			</p>
		</div>

	</div>
</div>