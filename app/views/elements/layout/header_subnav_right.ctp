<? if (isset($paginator) && $this->request->params['action'] == 'index') : ?>
	<span class="paginator">
		<?php
		$this->Paginator->options(array('url' => null));

			echo $this->Paginator->prev(
							'<span class="prev_img">&nbsp;</span>',
							array(
									'escape'	=> false,
									'rel'			=> 'prev',
							),
							null,
							array('style' => 'display:none;'));
		?>

		<span style="padding: 0 1px;">
			<?php echo $this->Paginator->current(); ?>
		</span>

		<?php
			echo $this->Paginator->next(
							'<span class="next_img">&nbsp;</span>',
							array(
									'escape'	=> false,
									'rel'			=> 'next',
							),
							null,
							array('style' => 'display:none;'));
		?>
	</span>
<? endif; ?>