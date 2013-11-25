	<?php
		echo $this->element('layout/html_header');
	?>
	</head>
	<body>
		<?php if (isset($CurrentUser) && !$CurrentUser->isLoggedIn() && $this->request->params['action'] != 'login') : ?>
			<?php echo $this->element('users/login_modal'); ?>
		<?php endif; ?>
	<div style ="min-height: 100%; position: relative;">
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
			<div class="navbar-content">
				<div class="navbar-left">
					<?php echo $this->fetch('headerSubnavLeft'); ?>
				</div>
				<div class="navbar-center">
					<?php echo $this->fetch('headerSubnavCenter'); ?>
				</div>
				<div class="navbar-right c_last_child">
					<?php echo $this->element('layout/header_subnav_right'); ?>
				</div>
			</div>
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
				<div class="navbar-content">
					<div class="navbar-left">
						<?php echo $this->fetch('headerSubnavLeft'); ?>
					</div>
					<div class="navbar-center">
						<a href="#" id="btn-scrollToTop" class="btn-hf-center">
							<i class="fa fa-arrow-up"></i>
						</a>
					</div>
					<div class="navbar-right c_last_child">
						<?php echo $this->element('layout/header_subnav_right'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="disclaimer" style="overflow:hidden;">
		<?php
			if( isset($showDisclaimer) ) {
				Stopwatch::start('layout/disclaimer.ctp');
				echo $this->element('layout/disclaimer');
				Stopwatch::stop('layout/disclaimer.ctp');
			}
		?>
	</div>
  <?php echo $this->element('layout/html_footer'); ?>
	</body>
</html>
