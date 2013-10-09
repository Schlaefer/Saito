<?php
	Stopwatch::start('shouts.ctp');
	if (empty($shouts)) {
		return;
	}
	$cache = Cache::read('Saito.Shouts.html');
	if (!empty($cache['lastId']) && $cache['lastId'] === $shouts[0]['Shout']['id']) :
		$shouts_html = $cache['html'];
	else:
		$shouts = $this->Shouts->prepare($shouts);
		$this->start('shouts');
		?>
		<div>
			<?php
				$i = 0;
				$time_was_output = false;
				foreach ($shouts as $shout) :
					$time = $this->TimeH->timeAgoInWordsFuzzy($shout['time']);
					?>
					<?php
					if ($time):
						?>
						<div class='info_text' style="">
							<?php
								echo $time;
								$time_was_output = true;
							?>
							&nbsp;
						</div>
					<?php elseif ($i > 0): ?>
						<hr/>
					<?php endif;
					$i = 1;
					?>
					<div class="shout">
					<span class="username">
						<?php
							echo $this->Html->link(
								$shout['user_name'],
								[
									'controller' => 'users',
									'action' => 'view',
									$shout['user_id']
								]
							);
						?>:
					</span>
						<?= $shout['html'] ?>
					</div>
				<?php
				endforeach;
				unset($i);
			?>
			<div class='info_text'>
				<?php echo $this->TimeH->timeAgoInWordsFuzzyGetLastTime(); ?>
			</div>
		</div>
		<?php
		$this->end('shouts');
		$shouts_html = $this->fetch('shouts');
		$cache = [
			'lastId' => $shouts[0]['id'],
			'html' => $shouts_html
		];
		Cache::write('Saito.Shouts.html', $cache);
	endif;
	echo $shouts_html;
	Stopwatch::end('shouts.ctp');
?>
