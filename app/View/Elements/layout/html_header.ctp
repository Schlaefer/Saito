<?php echo $this->Html->docType('xhtml-trans'); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
    <title><?php echo $title_for_layout ?></title>
		<?php echo $this->Html->charset(); ?>
		<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
		<?php
			echo $this->fetch('meta');
			echo $this->fetch('css');

			if (isset($autoPageReload)) {
				echo "<meta http-equiv='refresh' content='{$autoPageReload}'/>";
			};

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

			echo $this->Html->scriptBlock("
				var webroot = '{$this->request->webroot}';
				var Saito_Settings_embedly_enabled = " . $this->Js->value(Configure::read('Saito.Settings.embedly_enabled')) . ";
				var User_Settings_user_show_inline = " . $this->Js->value($CurrentUser['inline_view_on_click']) . ";
			");

			if (Configure::read('debug') == 0):
				echo $this->Html->script('lib/jquery/jquery-1.8.0.min');
			else:
				echo $this->Html->script('lib/jquery/jquery-1.8.0');
			endif;

			// require.js borks out when used with Cakes timestamp.
			// also we need the relative path for the main-script
			$tmp_asset_timestamp_cache = Configure::read('Asset.timestamp');
			Configure::write('Asset.timestamp', false);
			echo $this->Html->script('lib/require/require.min',
					array(
					'data-main' => $this->Html->assetUrl('main' . ((Configure::read('debug') == 0) ? '-prod' : ''),
							array(
							'pathPrefix' => JS_URL,
							'ext'				 => '.js'
					))
			));
			Configure::write('Asset.timestamp', $tmp_asset_timestamp_cache);
			unset($tmp_asset_timestamp_cache);
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