<?php echo $html->docType('xhtml-trans'); ?>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
			<title><?php echo $title_for_layout ?></title>
			<?php echo $html->charset(); ?>
			<?php # echo $html->meta(array( 'http-equiv' => 'expires', 'content' => '0'));?>
			<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
			<?php echo $html->meta( 'keywords', '');?>
			<?php echo $html->meta( 'description', '');?>
			<?php if(isset($autoPageReload)) : ?>
				<meta http-equiv='refresh' content='<?php echo $autoPageReload; ?>' />
			<?php endif; ?>
			<link rel="apple-touch-icon" href="<?php echo $this->webroot.'theme'.DS.$this->theme.DS.IMAGES_URL.'apple-touch-icon-precomposed.png';?>"/>
			<script type="text/javascript">
      //<![CDATA[
				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
					document.write('<meta name="viewport" content="width=600" />');
				}
      //]]>
	 		</script>
			<?php echo $html->css('stylesheets/jquery-ui-1.8.8.custom.css'); ?>
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
			<meta name="viewport" content="width=device-width" />