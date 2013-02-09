<?php foreach($shouts as $shout): ?>
			<?php echo $this->TimeH->formatTime($shout['Shout']['created']);  ?>
			<span class="username">
				<?php echo $shout['User']['username']; ?>:
			</span>
		<?php
			echo $this->Bbcode->parse(
				$shout['Shout']['text'],
				array('multimedia' => false)
			);
		?>
		<hr/>

<?php endforeach; ?>