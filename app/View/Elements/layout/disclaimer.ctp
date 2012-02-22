<div class="bp_four_column" style='width: 951px; margin: 0 auto; position: relative;'>
	<?php // @lo this .ctp ?>
	<div class="left">
		<div class="inside">
			<h3> I </h3>
		</div>
	</div>
	<div class="center_l">
		<div class="inside">
			<h3> II </h3>
		</div>
	</div>
	<div class="center_r">
		<div class="inside">
			<h3> Status </h3>
			<?= number_format($HeaderCounter['entries'],
					null, null, '.') ?> Entries in
			<?= number_format($HeaderCounter['threads'],
					null, null, '.') ?> Threads;
			<?= number_format($HeaderCounter['user'],
					null, null, '.') ?> registered users,
			<?= $HeaderCounter['user_registered'] ?> logged in,
			<?= $HeaderCounter['user_anonymous'] ?> anonymous.
		</div>
	</div>
	<div class="right">
		<div class="inside">
			<h3> About </h3>
			<p>
				Powered by <a href="https://github.com/Schlaefer/Saito">Saito</a> v<?php echo Configure::read("Saito.v"); ?>.
			</p>
		</div>
	</div>
</div>