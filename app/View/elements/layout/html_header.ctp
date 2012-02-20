<?php echo $this->Html->docType('xhtml-trans'); ?>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
			<title><?php echo $title_for_layout ?></title>
			<?php echo $this->Html->charset(); ?>
			<?php # echo $this->Html->meta(array( 'http-equiv' => 'expires', 'content' => '0'));?>
			<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
			<?php echo $this->Html->meta( 'keywords', '');?>
			<?php echo $this->Html->meta( 'description', '');?>
			<?php if(isset($autoPageReload)) : ?>
				<meta http-equiv='refresh' content='<?php echo $autoPageReload; ?>' />
			<?php endif; ?>
			<link rel="apple-touch-icon" href="<?php echo $this->request->webroot.'theme'.DS.$this->theme.DS.IMAGES_URL.'apple-touch-icon-precomposed.png';?>"/>
			<script type="text/javascript">
      //<![CDATA[
				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
					document.write('<meta name="viewport" content="width=600" />');
				}
      //]]>
	 		</script>
			<?php echo $this->Html->css('stylesheets/jquery-ui-1.8.8.custom.css'); ?>
			<?php echo $this->Html->css('stylesheets/screen.css'); ?>
			<?php if(Configure::read('debug') > 0) echo $this->Html->css('stylesheets/cake.css'); ?>
			<?php
				if (is_file(APP.'View'.DS."Themed".DS.$this->theme.DS.'webroot'.DS."css".DS.'stylesheets'.DS.$this->request->params["controller"].DS.$this->request->params["action"].".css")) {
							 echo $this->Html->css('stylesheets/'.$this->request->params["controller"]."/".$this->request->params["action"]);
				}
			?>
			<?php
				if ( $CurrentUser->isLoggedIn() ) :
					echo $this->UserH->generateCss($CurrentUser->getSettings());
				endif;
				?>
			<?php echo $this->Html->script('jquery-1.6.1.min'); ?>
			<meta name="viewport" content="width=device-width" />