<?php if (isset($this->Paginator) && $this->request->params['action'] == 'index') : ?>
	<span class="paginator">
		<?php
		$this->Paginator->options(array('url' => null));

			echo $this->Paginator->prev(
							'<i class="icon-chevron-left"></i>',
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
							'<i class="icon-chevron-right"></i>',
							array(
									'escape'	=> false,
									'rel'			=> 'next',
							),
							null,
							array('style' => 'display:none;'));
		?>
	</span>
<?php endif; ?>