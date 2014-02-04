<?php
	echo $this->element('layout/html_header');
?>
</head>
<body>
<?php if (isset($CurrentUser) && !$CurrentUser->isLoggedIn() && $this->request->params['action'] != 'login') : ?>
	<?php echo $this->element('users/login_modal'); ?>
<?php endif; ?>
<div class="body">
	<div id="top" class="l-top hero">
		<div class="l-top-right hero-text">
			<?php echo Stopwatch::start('header_search.ctp'); ?>
			<?php if ($CurrentUser->isLoggedIn()) {
				echo $this->element('layout/header_search', ['cache' => '+1 hour']);
			} ?>
			<?php echo Stopwatch::stop('header_search.ctp'); ?>
		</div>
		<div class="l-top-left hero-text">
			<?php
				echo $this->Html->link(
						$this->Html->image(
								'forum_logo.png',
								['alt' => 'Logo', 'height' => 70]
						),
						'/' . (isset($markAsRead) ? '?mar' : ''),
						$options = [
								'id' => 'btn_header_logo',
								'escape' => false,
						]
				);
			?>
		</div>
	</div>
	<div class="l-top-menu-wrapper">
		<div class="l-top-menu top-menu">
			<?= $this->element('layout/header_login', ['divider' => '|']); ?>
		</div>
	</div>
	<div id="topnav" class="navbar">
		<?=
			$this->Layout->heading([
							'first' => $this->fetch('headerSubnavLeft'),
							'middle' => '<a href="#" id="btn-scrollToTop" class="btn-hf-center"><i class="fa fa-arrow-up"></i></a>',
							'last' => $this->element('layout/header_subnav_right')
					],
					['class' => 'navbar-content'])
		?>
	</div>
	<?php echo $this->element('layout/slidetabs'); ?>
	<div id="content">
		<script type="text/javascript">
			if (!SaitoApp.request.isPreview) { $('#content').css('visibility', 'hidden'); }
		</script>
		<?php echo $this->fetch('content'); ?>
	</div>
	<div id="footer-pinned">
		<div id="bottomnav" class="navbar">
			<?=
				$this->Layout->heading([
								'first' => $this->fetch('headerSubnavLeft'),
								'last' => $this->element('layout/header_subnav_right')
						],
						['class' => 'navbar-content'])
			?>
		</div>
	</div>
</div>
<div class="disclaimer" style="overflow:hidden;">
	<?php
		if (isset($showDisclaimer)) {
			Stopwatch::start('layout/disclaimer.ctp');
			echo $this->element('layout/disclaimer');
			Stopwatch::stop('layout/disclaimer.ctp');
		}
	?>
</div>
<?php echo $this->element('layout/html_footer'); ?>
</body>
</html>
