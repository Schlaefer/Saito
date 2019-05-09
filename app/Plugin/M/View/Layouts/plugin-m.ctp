<!DOCTYPE html>
<html<?= (!$isDebug) ? ' manifest="' . $this->Html->url('cache.manifest') . '"' : ''; ?>>
<head>
	<title><?= $title_for_layout ?></title>
	<meta name="apple-mobile-web-app-title" content="<?= $short_title_for_layout ?>">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="initial-scale=1.0">
	<?php
		$cssAssets = [
			'M.dist/common',
			'M.dist/theme'
		];
		if (!$isDebug) {
			array_unshift($cssAssets, 'M.dist/app.min');
		}
		$callback = function ($asset) {
			return $this->Html->assetUrl($asset, ['ext' => '.css', 'fullBase' => true]);
		};
		$cssAssets = array_map($callback, $cssAssets);
		echo $this->Html->css($cssAssets);
	?>
	<script>
		window.Saito = {
			webroot: "<?= $this->webroot ?>",
			callbacks: { }
		};
	</script>
	<?= $this->element('custom_html_header') ?>
  <?php if ($isDebug) { ?>
    <meta HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Expires" CONTENT="-1">
  <?php } ?>
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

<?php if ($isDebug) : ?>
	<?= $this->Html->script('M.../dev/jspm_packages/system.src') ?>
	<script>
		System.baseURL = window.Saito.webroot + 'm/dev/';
	</script>
	<?= $this->Html->script('M.../dev/config') ?>
	<script>
		System.baseURL = window.Saito.webroot + 'm/dev/';
		System.import('js/app/app');
	</script>
<?php else : ?>
	<?= $this->Html->script('M.../dist/app.min.js') ?>
<?php endif; ?>
</body>
</html>
