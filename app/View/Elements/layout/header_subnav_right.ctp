<?php
  echo $this->fetch('headerSubnavRightTop');
	echo $this->assign('headerSubnavRightTop', '');
  echo $this->fetch('headerSubnavRight');
?>
<?php // if a page has a global paginator we assume it's always shown top right ?>
<?php if (isset($this->Paginator) && $this->request->params['action'] == 'index') : ?>
	<span class="paginator navbar-item right">
		<?php
		$this->Paginator->options(array('url' => null));

			echo $this->Paginator->prev(
							'<i class="fa fa-chevron-left"></i>',
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
							'<i class="fa fa-chevron-right"></i>',
							array(
									'escape'	=> false,
									'rel'			=> 'next',
							),
							null,
							array('style' => 'display:none;'));
		?>
	</span>
<?php endif; ?>