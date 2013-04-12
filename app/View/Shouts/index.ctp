<div>
	<?php
		$i = 0;
		$time_was_output= false;
		if (!empty($shouts)) :
			foreach($shouts as $shout) :
				$time = $this->TimeH->timeAgoInWordsFuzzy($shout['Shout']['time']);
				?>
				<?php
				if ($time):
				?>
						<div class='info_text' style="">
								<?php
									echo $time;
									$time_was_output= true;
								?>
								&nbsp;
					</div>
				<?php elseif($i > 0): ?>
						<hr/>
				<?php endif;
				$i = 1;
				?>
				<div class="shout">
					<span class="username">
						<?php
						echo $this->Html->link(
								$shout['User']['username'],
								array('controller' => 'users', 'action' => 'view', $shout['User']['id']  )
							);
						?>:
					</span>
					<?php
					echo $this->Bbcode->parse(
						$shout['Shout']['text'],
						array('multimedia' => false)
					);
					?>
				</div>
						<?php
			endforeach;
			unset($i);
			?>
			<div class='info_text'>
				<?php echo $this->TimeH->timeAgoInWordsFuzzyGetLastTime(); ?>
			</div>
	<?php
		endif;
	?>
</div>