<?php echo $html->docType('xhtml-trans'); ?>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
			<title><?php echo $title_for_layout ?></title>
			<?php echo $html->charset(); ?>
			<script type="text/javascript">
      //<![CDATA[
				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {

					document.write('<meta name="viewport" content="width=600" />');
				}
      //]]>
	 		</script>
			<?php echo $html->css('stylesheets/screen.css'); ?>
			<?php if(Configure::read('debug') > 0) echo $html->css('stylesheets/cake.css'); ?>
			<?php
				if (is_file(APP.WEBROOT_DIR.DS."theme".DS.$this->theme.DS."css".DS.'stylesheets'.DS.$this->params["controller"].DS.$this->params["action"].".css")) {
							 echo $html->css('stylesheets/'.$this->params["controller"]."/".$this->params["action"]);
				}
			?>
			<?php
				if ( $CurrentUser->isLoggedIn() ) :
					echo $userH->generateCss($CurrentUser->getSettings());
				endif;
				?>
			<?php echo $html->script('jquery-1.6.1.min'); ?>
	</head>
	<body>
	<div style ="min-height: 100%; position: relative;">
				<?php echo $this->Session->flash(); ?>
				<?php echo $content_for_layout ?>
		<?php echo $this->element('sql_dump'); ?>
	</div>

	<?php echo $html->scriptBlock("var webroot = '$this->webroot';"); ?>
	<?php
		echo $html->scriptBlock("var user_show_inline = '{$this->Session->read('Auth.User.inline_view_on_click')}';");
	?>
	<?php echo $html->script('js.min'); ?>
	<?php # echo $html->script('jquery-ui-1.8.2.custom.min'); ?>
	<?php echo $html->script('_app'); ?>
	<?php # echo $html->script('classes/thread.class'); ?>
	<?php # echo $html->script('classes/thread_line.class'); ?>
	<?php echo $scripts_for_layout; ?>
	<? echo $js->writeBuffer();?>
	</body>
</html>
