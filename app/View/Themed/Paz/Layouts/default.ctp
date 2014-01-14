	<?php
		// @todo; also look out for/fix custom colors li-bullets
		$this->Blocks->set('css', '');

		echo $this->element('layout/html_header');

		echo $this->Html->css('stylesheets/static.css');
		if ((time() > date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, 51.5, 9.9) &&
				time() < date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, 51.5, 9.9))
		) {
			// echo $this->Html->css('stylesheets/theme');
			$_theme = 'day';
		} else {
			// echo $this->Html->css('stylesheets/night');
			$_theme = 'night';
		}
	?>
	<script>
		var theme = '<?= $_theme ?>',
				preset = localStorage.theme;
				css = 'theme';
		if (!preset) {
			preset = 'automatic'
		}
		if (preset !== 'automatic') {
			theme = preset;
		}
		if (theme === 'night') {
			css = 'night'
		}
		document.write('<link rel="stylesheet" type="text/css" href="' + SaitoApp.app.settings.webroot + 'theme/Paz/css/stylesheets/' + css + '.css" />');
		SaitoApp.app.settings.themePreset = preset;
	</script>
	<link href="//fonts.googleapis.com/css?family=Cabin:400,400italic,500italic,500,600italic,600,700italic,700" rel="stylesheet" type="text/css">
	</head>
<body class="l-body">
	<script>
		var _headerClosed = localStorage.headerClosed;
		if (_headerClosed === 'true') {
			$('body').addClass('headerClosed');
		}
	</script>
	<?php if (isset($CurrentUser) && !$CurrentUser->isLoggedIn() && $this->request->params['action'] != 'login') : ?>
		<?php echo $this->element('users/login_modal'); ?>
	<?php endif; ?>
	<div id="site">
		<header>
			<div id="hero">
				<?php
					$_title = '<div id="hero-home-link">' . h($forum_name) . '</div>';
					echo $this->Html->link(
						$_title,
							'/' . (isset($markAsRead) ? '?mar' : ''),
						$options = [
							'id' => 'btn_header_logo',
							'escape' => false,
						]
					);
				?>
				<button id="js-top-menu-open" class="btnLink top-menu-item">
					<i class="fa fa-plus-square-o"></i>
				</button>
			</div>
			<div class="top-menu">
				<div class="top-menu-body">
					<?= $this->element('layout/header_login', ['divider' => '']); ?>
					<?= $this->Html->link(
						'<i class="fa fa-search"></i> ' . h(__('Search')),
						'/entries/search',
						['class' => 'top-menu-item', 'escape' => false]);
					?>
					<span style="position: absolute; right:0">
						<button id="js-themeSwitcher" class="btnLink top-menu-item"></button>
						<button id="js-top-menu-close" class="btnLink top-menu-item">
							<i class="fa fa-minus-square-o"></i>
						</button>
					</span>
				</div>
			</div>
		</header>
		<div class="navbar">
			<div class="navbar-body">
				<div class="navbar-left">
					<?php echo $this->fetch('headerSubnavLeft'); ?>
				</div>
				<div class="navbar-center">
					<?php
						if ($this->request->controller !== 'entries' ||
								!in_array($this->request->action, ['mix', 'view'])) {
							$_navCenter = $this->fetch('headerSubnavCenter');
							if (empty($_navCenter)) {
								$_navCenter = $title_for_page;
							}
							echo $_navCenter;
						}
					?>
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
	<?php if (isset($showDisclaimer)) : ?>
		<div class="disclaimer" style="overflow:hidden;">
			<?php
				Stopwatch::start('layout/disclaimer.ctp');
				echo $this->element('layout/disclaimer');
				Stopwatch::stop('layout/disclaimer.ctp');
			?>
		</div>
	<?php endif; ?>
<?php echo $this->element('layout/html_footer'); ?>
	<script>
		require(['common'], function(){
			require([SaitoApp.app.settings.webroot + 'theme/Paz/js/theme.js']);
			require([SaitoApp.app.settings.webroot + 'theme/Paz/js/theme-switcher.js'], function(TS) {
				new TS({preset: SaitoApp.app.settings.themePreset});
			});
		})
	</script>
</body>
</html>
