<!DOCTYPE html>
<html<?= (!$isDebug) ? ' manifest="' . $this->Html->url(
			'cache.manifest'
		) . '"' : ''; ?>>
<head>
	<title><?= $title_for_layout ?></title>
	<meta name="apple-mobile-web-app-title" content="<?= $short_title_for_layout ?>">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="initial-scale=1.0">
	<?= $this->Html->css(['M.../dist/common.css', 'M.../dist/theme.css']) ?>
	<script>
		window.Saito = {
			webroot: "<?= $this->webroot ?>"
		};
	</script>
	<?php
		if ($isDebug) {
			// $requireJsScript = 'main-prod';
			$requireJsScript = 'main';
			echo $this->RequireJs->scriptTag($requireJsScript, ['jsUrl' => 'm/dev/js/']);
			?>
			<meta HTTP-EQUIV="Pragma" CONTENT="no-cache">
			<meta HTTP-EQUIV="Expires" CONTENT="-1">
		<?php
		} else {
			echo $this->Html->script('M.../dist/js.js');
		} ?>
</head>
<body>
<div id="main">
	<div class="container container-wrapper">
		<div class="container container-index"></div>
		<div class="container container-mix hidden"></div>
		<div class="container container-chat hidden"></div>
	</div>
</div>
<div id="card-bottom"></div>
</body>
</html>
