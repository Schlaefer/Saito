<?php echo $this->Html->docType('xhtml-trans'); ?>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
      <title><?php echo $title_for_layout ?></title>
			<?php echo $this->Html->charset(); ?>
			<script type="text/javascript">
      //<![CDATA[
				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {

					document.write('<meta name="viewport" content="width=600" />');
				}
      //]]>
	 		</script>
			<?php echo $this->Html->css('stylesheets/static.css'); ?>
			<?php echo $this->Html->css('stylesheets/styles.css'); ?>
			<?php if($isDebug) echo $this->Html->css('stylesheets/cake.css'); ?>
			<?php
				if (is_file(APP.'View'.DS."Themed".DS.$this->theme.DS.'webroot'.DS."css".DS.'stylesheets'.DS.$this->request->params["controller"].DS.$this->request->params["action"].".css")) {
							 echo $this->Html->css('stylesheets/'.$this->request->params["controller"]."/".$this->request->params["action"]);
				}
			?>
			<?php
				if ( $CurrentUser->isLoggedIn() ) :
					echo $this->UserH->generateCss($CurrentUser->getSettings());
				endif;
				$SaitoApp = array (
					'app' => array(
						'webroot' => $this->request->webroot,
						),
					);
					echo $this->Html->scriptBlock('var SaitoApp = ' . json_encode($SaitoApp));
					echo $this->jQuery->scriptTag();
				?>
	</head>
	<body>
	<div style ="min-height: 100%; position: relative;">
				<?php echo $this->Session->flash(); ?>
				<?php echo $this->fetch('content'); ?>
		<?php echo $this->element('sql_dump'); ?>
	</div>

	<?php
		echo $this->Html->scriptBlock("var user_show_inline = '{$this->Session->read('Auth.User.inline_view_on_click')}';");
	?>
  <?php echo $this->fetch('script'); ?>
	<?php echo $this->Js->writeBuffer();?>
	</body>
</html>
