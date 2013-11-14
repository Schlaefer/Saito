CACHE MANIFEST
<?php
	$_customCtpPath = 'Elements/custom_html_header.ctp';
	// default place in plugin
	$_ctpPath = App::pluginPath('M') . 'View' . DS . $_customCtpPath;
	$_ctpPathTheme = App::themePath($this->theme) . 'Plugin' . DS . 'M' .
			DS . $_customCtpPath;
	if (file_exists($_ctpPathTheme)) {
		$_ctpPath = $_ctpPathTheme;
	}
	$touch = md5(
		Configure::read('debug') .
		// static location in plugin path
		filemtime(App::pluginPath('M') . 'webroot/touch.txt') .
		filemtime(App::pluginPath('M') . 'webroot/dist/js.js') .
		filemtime(App::pluginPath('M') . 'webroot/dist/common.css') .
		// dynamic location in plugin or theme path
		filemtime($_ctpPath) .
		$this->Html->getAssetTimestamp($this->Html->webroot('M/dist/theme.css'))
	);
?>

# Version: <?= $touch ?>


CACHE:
/favicon.ico
<?php
	$assets = [
		'M.dist/common.css',
		'M.dist/theme.css',
		'M.dist/js.js',
		'M.dist/font/fontawesome-webfont.woff'
	];
	foreach ($assets as $asset) {
		echo $this->Html->assetUrl($asset) . "\n";
	}
?>

NETWORK:
*

FALLBACK:
