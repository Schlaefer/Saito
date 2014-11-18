<?= $this->element('layout/html_header') ?>
		<link href='//fonts.googleapis.com/css?family=Fenix' rel='stylesheet' type='text/css'>
		<link href="//fonts.googleapis.com/css?family=Cabin:400,400italic,500italic,500,600italic,600,700italic,700" rel="stylesheet" type="text/css">
		<?= $this->Html->css('stylesheets/static.css') ?>
		<script>
			(function(SaitoApp) {
				var css = 'theme';
				try {
					preset = localStorage.theme;
					if (preset && preset === 'night') {
						css = 'night';
					}
				} catch (e) {}
				document.write('<link rel="stylesheet" type="text/css" href="' + SaitoApp.app.settings.webroot + 'Paz/css/stylesheets/' + css + '.css" />');
				SaitoApp.app.theme = {preset: css};
			})(SaitoApp);
		</script>
        <noscript>
            <?= $this->Html->css('Paz.stylesheets/theme.css') ?>
        </noscript>
	</head>
<body class="l-body">
	<script>
		var _headerClosed = localStorage.headerClosed;
		if (_headerClosed === 'true') {
			$('body').addClass('headerClosed');
		}
	</script>
	<?php if (!$CurrentUser->isLoggedIn() && $this->request->params['action'] != 'login') : ?>
		<?php echo $this->element('users/login_modal'); ?>
	<?php endif; ?>
	<div id="site">
		<header id="site-header">
			<div id="hero">
				<?php
					$homeLink = '<div id="hero-home-link">' . h($forum_name) . '</div>';
					$options = ['id' => 'btn_header_logo'];
					// note: don't change the next line or you may break Mark-As-Read
					echo $this->Html->link($homeLink, '/' . (isset($markAsRead) ? '?mar' : ''), $options + ['escape' => false]);
				?>
				<button id="js-top-menu-open" class="btnLink top-menu-item">
					<i class="fa fa-plus-square-o"></i>
				</button>
			</div>
			<div class="top-menu">
				<div class="top-menu-body">
          <?= $this->element('layout/header_login', ['divider' => '']); ?>
          <?php
          /*
            // @todo 3.0
            echo $this->Html->link(
              $this->Layout->textWithIcon(h(__('Search')), 'search'),
              '/searches/simple',
              ['class' => 'top-menu-item', 'escape' => false]);
          */
          ?>
					<span class="top-menu-aside">
						<button id="shp-show" class="btnLink shp-show-btn top-menu-item">
							<i class="fa fa-question-circle"></i>
						</button>
						<button id="js-themeSwitcher" class="btnLink top-menu-item"></button>
						<button id="js-top-menu-close" class="btnLink top-menu-item">
							<i class="fa fa-minus-square-o"></i>
						</button>
					</span>
				</div>
			</div>
		</header>
		<?php
			$_navCenter = '';
			if ($this->request->controller !== 'entries' ||
					!in_array($this->request->action, ['mix', 'view'])) {
				$_navCenter = $this->fetch('headerSubnavCenter');
				if (empty($_navCenter)) {
					$_navCenter = $this->Layout->pageHeading($title_for_page);
				}
			}

			echo $this->Layout->heading([
							'first' => $this->fetch('headerSubnavLeft'),
							'middle' => $_navCenter,
							'last' => $this->element('layout/header_subnav_right')
					],
					['class' => 'navbar', 'escape' => false]);

			if ($this->get('showSlidetabs')) {
				if (!empty($slidetabs)) {
					\Stopwatch\Lib\Stopwatch::start('Slidetabs');
					// @todo 3.0 remove migration array
					$migrationHelper = [
						'slidetab_userlist' => 'SlidetabUserlist',
						'slidetab_shoutbox' => 'SlidetabShoutbox'
					];
					echo '<aside id="slidetabs">';
					foreach ($slidetabs as $slidetab) {
						echo $this->cell($migrationHelper[$slidetab]);
					}
					echo '</aside>';
					\Stopwatch\Lib\Stopwatch::end('Slidetabs');
				}
			}
		?>

		<div id="content">
			<script type="text/javascript">
				if (!SaitoApp.request.isPreview) { $('#content').css('visibility', 'hidden'); }
			</script>
			<?php echo $this->fetch('content'); ?>
		</div>
		<?php if ($this->request->controller === 'entries' && $this->request->action === 'index'): ?>
			<div id="footer-pinned">
				<div id="bottomnav" class="navbar">
					<?=
						$this->Layout->heading([
										'first' => $this->fetch('headerSubnavLeft'),
										'middle' => '<a href="#" id="btn-scrollToTop" class="btn-hf-center"><i class="fa fa-arrow-up"></i></a>',
										'last' => $this->element('layout/header_subnav_right')
								],
								['class' => 'navbar-content', 'escape' => false])
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if (isset($showDisclaimer)) : ?>
		<div class="disclaimer">
			<?= $this->element('layout/disclaimer') ?>
		</div>
	<?php endif; ?>
<?= $this->element('layout/html_footer'); ?>
	<script>
		SaitoApp.callbacks.afterAppInit.push(function() {
			require([SaitoApp.app.settings.webroot + 'Paz/js/theme.js']);
		});
	</script>
</body>
</html>
