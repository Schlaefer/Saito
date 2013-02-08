<?php echo $this->Html->docType('xhtml-trans'); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
    <title><?php echo $title_for_layout ?></title>
		<?php echo $this->Html->charset(); ?>
		<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
		<?php
			echo $this->fetch('meta');
			echo $this->fetch('css');

			echo $this->Html->css('stylesheets/static.css');
			echo $this->Html->css('stylesheets/styles.css');

			if (Configure::read('debug') > 0)
				echo $this->Html->css('stylesheets/cake.css');
			if (is_file(APP . 'View' . DS . "Themed" . DS . $this->theme . DS . 'webroot' . DS . "css" . DS . 'stylesheets' . DS . $this->request->params["controller"] . DS . $this->request->params["action"] . ".css")) {
				echo $this->Html->css('stylesheets/' . $this->request->params["controller"] . "/" . $this->request->params["action"]);
			}

			if (isset($CurrentUser) && $CurrentUser->isLoggedIn()) :
				echo $this->UserH->generateCss($CurrentUser->getSettings());
			endif;
			echo $this->Html->scriptBlock($this->Html->getAppJs($this));
			echo $this->jQuery->scriptTag();
			if (Configure::read('debug') == 0):
				echo $this->Html->script('lib/jquery/jquery-1.9.0.min');
				echo $this->RequireJs->scriptTag('main-prod');
			else:
				echo $this->RequireJs->scriptTag('main');
			endif;
		?>
		<?php
			/*
			 * fixing safari mobile fubar;
			 * see: http://stackoverflow.com/questions/6448465/jquery-mobile-device-scaling
			 */
			?>
		<meta name="viewport" content="height=device-height,width=device-width" />
		<script type="text/javascript">
			//<![CDATA[
    if (navigator.userAgent.match(/iPad/i)) {
        var $viewport = $('head').children('meta[name="viewport"]');
        $(window).bind('orientationchange', function() {
            if (window.orientation == 90 || window.orientation == -90 || window.orientation == 270) {
                $viewport.attr('content', 'height=device-width,width=device-height,initial-scale=1.0,maximum-scale=1.0');
            } else {
                $viewport.attr('content', 'height=device-height,width=device-width,initial-scale=1.0,maximum-scale=1.0');
            }
        }).trigger('orientationchange');
    }
			//]]>
		</script>