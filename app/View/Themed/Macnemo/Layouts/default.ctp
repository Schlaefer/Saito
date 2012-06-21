	<?php
		echo $this->element('layout/html_header');
	?>
		<link rel="apple-touch-icon" href="<?php echo $this->request->webroot . 'theme' . DS . $this->theme . DS . IMAGES_URL . 'apple-touch-icon-precomposed.png'; ?>"/>
	</head>
	<body>
		<?php if (!$CurrentUser->isLoggedIn() && $this->request->params['action'] != 'login') : ?>
			<div id='modalLoginDialog' style='height: 0px; overflow: hidden;'>
				<?php echo $this->element('users/login_form'); ?>
			</div>
		<?php endif; ?>
	<div style ="min-height: 100%; position: relative;">
		<div id="top" class="l-top hero" >
				<div class="l-top-spnsr">
 					<a href="/wiki/Main/Unterst%c3%bctzen" title="Spenden"><?php echo $this->Html->image('forum_logo_badge.png', array( 'alt' => 'Spenden', 'width' => '80', 'height' => '70')); ?></a>

				</div>
				<div class="l-top-right hero-text">
						<?php echo Stopwatch::start('header_search.ctp');?>
							<?php if ( $CurrentUser->isLoggedIn() ) { echo $this->element('layout/header_search', array('cache' => '+1 hour')); } ?>
						<?php echo Stopwatch::stop('header_search.ctp');?>
				</div> <!-- .right -->
        <div class="l-top-left hero-text">
          <?php
            echo $this->Html->link(
                $this->Html->image(
                    'forum_logo.png', array( 'alt' => 'Logo', 'height' => 70 )
                ),
                array( 'controller' => 'entries', 'action' => 'index', 'admin' => false ),
                array( 'id' => 'btn_header_logo', 'escape' => false ));
          ?>
          <div id="claim"></div>
				</div> <!-- .left -->
				<div class="l-top-menu top-menu">
						<?php echo $this->element('layout/header_login'); ?>
				</div>
		</div> <!-- #top -->
		<div id="topnav" class="navbar">
			<div>
				<div>
          <?php echo $this->fetch('headerSubnavLeft'); ?>
				</div>
				<div>
          <?php echo $this->fetch('headerSubnavCenter'); ?>
				</div>
				<div class="c_last_child">
					<?php echo $this->element('layout/header_subnav_right'); ?>
				</div>
			</div>
		</div>
		<?php 
		$flashMessage = $this->Session->flash();
		$emailMessage = $this->Session->flash('email'); 
		if ($flashMessage || $emailMessage) : 
		?>
			<div id="l-flash-container">
				<?php echo $flashMessage; ?>
				<?php echo $emailMessage; ?>
			</div>
		<?php endif; ?>
		<?php echo $this->element('layout/slidetabs'); ?>
		<div id="content">
				<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer-pinned">
			<div id="bottomnav" class="navbar">
				<div>
						<div>
              <?php echo $this->fetch('headerSubnavLeft'); ?>
						</div>
						<div>
							<a href="javascript:window.scrollTo(0,0);" style="width: 100px; display: inline-block; height: 20px;"><i class="icon-arrow-up"></i></a>
						</div>
						<div class="c_last_child">
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
  <div>
		<?php echo $this->Html->scriptBlock("var webroot = '{$this->request->webroot}'; var user_show_inline = '{$CurrentUser['inline_view_on_click']}';"); ?>
		<?php 
			if ( Configure::read('debug') == 0 ):
				echo $this->Html->script('js.min');
				echo $this->Html->script(
						array(
								'js2-min.js',
								)
						);
			else:
				echo $this->Html->script('jquery.hoverIntent.minified');
				echo $this->Html->script('lib/jquery-ui/jquery-ui-1.8.19.custom.min');
				echo $this->Html->script('classes/thread.class');
				echo $this->Html->script('classes/thread_line.class');
				echo $this->Html->script('_app');
				echo $this->Html->script('jquery.form');
				echo $this->Html->script('jquery.scrollTo-1.4.2-min');
				echo $this->Html->script(
						array(
                'bootstrap/bootstrap.js',
								'lib/underscore/underscore.js',
								'lib/backbone/backbone.js',
								'lib/backbone/backbone.localStorage',
								'_appbb'
								)
						);
			endif;
		?>
		<?php echo $this->fetch('script'); ?>
		<?php echo $this->Js->writeBuffer();?>
		<div class='clearfix'></div>
		<?php
			if ( (int)Configure::read('debug') !== 0 ) :
        echo $this->Stopwatch->getResult();
      endif;
      ?>
		<?php echo $this->element('sql_dump'); ?>
	</div>
	</body>
</html>