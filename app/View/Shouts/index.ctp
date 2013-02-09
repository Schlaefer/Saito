<?php foreach($shouts as $shout): ?>
			<?php echo $this->TimeH->formatTime($shout['Shout']['created']);  ?>
			<span class="username" style="font-weight:bold;">
				<?php echo $shout['User']['username']; ?>:
			</span>
		<?php
			echo $this->Bbcode->parse($shout['Shout']['text']);
		?>
		<hr/>

<?php endforeach; ?>