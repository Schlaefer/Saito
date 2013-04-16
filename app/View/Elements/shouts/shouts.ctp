<?php
Stopwatch::start('shouts.ctp');
if (!empty($shouts)) :
	$cache = Cache::read('Shouts.html');
	if ($cache && $shouts[0]['Shout']['id'] === $cache['lastId']) {
		$shouts_html = $cache['html'];
	} else {
		$this->start('shouts');
		?>
		<div>
			<?php
			$i = 0;
			$time_was_output = false;
			foreach ($shouts as $shout) :
				$time = $this->TimeH->timeAgoInWordsFuzzy($shout['Shout']['time']);
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
							$shout['User']['username'],
							array(
								'controller' => 'users',
								'action'     => 'view',
								$shout['User']['id']
							)
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
		</div>
		<?php
		$shouts_html = $this->end('shouts');
		$cache = [
			'lastId' => $shouts[0]['Shout']['id'],
			'html'   => $shouts_html
		];
		Cache::write('Shouts.html', $cache);
	}
	echo $shouts_html;
endif;
Stopwatch::end('shouts.ctp');
?>
