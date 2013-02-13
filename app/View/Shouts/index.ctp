<div>
	<?php
		$i = count($shouts);
		foreach($shouts as $shout): ?>
			<div class="shout" data-id="<?php echo $shout['Shout']['id'] ?>">
				<span class="username">
					<?php echo $shout['User']['username']; ?>:
				</span>
				<?php
				echo $this->Bbcode->parse(
					$shout['Shout']['text'],
					array('multimedia' => false)
				);
				?>
					<span class="info_text">
					<?php echo $this->TimeH->formatTime($shout['Shout']['created']); ?>
				</span>
			</div>
			<?php
			if ($i !== 1):
				$i--;
				?>
          <hr/>
				<?php
			endif;
		endforeach;
		unset($i);
	?>
</div>