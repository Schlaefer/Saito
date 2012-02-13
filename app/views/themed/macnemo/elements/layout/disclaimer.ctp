<div class="bp_four_column" style='width: 951px; margin: 0 auto; position: relative;'>
	<?php // @lo this .ctp ?>
	<div class="left">
		<div class="inside">
			<h3> Ressourcen </h3>
			<ul>
				<li>
					<a href="http://macnemo.de/wiki/">Wiki</a>
				</li>
				<li>
					<a href="<?PHP echo $this->request->webroot ?>users/contact/1">Kontakt</a><!-- @lo  -->
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
				<li>
					<a href="<?php echo $this->request->webroot; ?>mobile/entries/index">Mobile (Tech-Demo)</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="center_l">
		<div class="inside">
			<h3> Status </h3>
			<?= number_format($HeaderCounter['entries'],
					null, null, '.') ?> mal Seemannsgarn in
			<?= number_format($HeaderCounter['threads'],
					null, null, '.') ?> unglaublichen Geschichten;
			<?= number_format($HeaderCounter['user'],
					null, null, '.') ?> geheuert,
			<?= $HeaderCounter['user_registered'] ?> an Deck,
			<?= $HeaderCounter['user_anonymous'] ?> Blinde Passagiere.
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
				Angetrieben durch <a href="https://github.com/Schlaefer/Saito">Saito</a> v<?php echo Configure::read("Saito.v"); ?>.
			</p>
			<p>
				<a href="http://www.google.com/moderator/#15/e=d490b&t=d490b.40">Feedback geben.</a>
			</p>
		</div>

	</div>
</div>