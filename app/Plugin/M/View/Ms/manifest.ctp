CACHE MANIFEST
# Version: <?= $touch ?>


CACHE:
/favicon.ico
<?php
	$assets = [
		'M.dist/styles.css',
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
