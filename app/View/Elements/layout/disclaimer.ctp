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
			<?php echo  number_format($HeaderCounter['entries'],
					null, null, '.') ?> Entries in
			<?php echo  number_format($HeaderCounter['threads'],
					null, null, '.') ?> Threads;
			<?php echo  number_format($HeaderCounter['user'],
					null, null, '.') ?> registered users,
			<?php echo  $HeaderCounter['user_registered'] ?> logged in,
			<?php echo  $HeaderCounter['user_anonymous'] ?> anonymous.
		</div>
	</div>
	<div class="right">
		<div class="inside">
			<h3> About </h3>
			<p>
				<a href="http://saito.siezi.com/">Powered by Saito  v<?php echo Configure::read("Saito.v"); ?></a>.
			</p>
		</div>
	</div>
</div>