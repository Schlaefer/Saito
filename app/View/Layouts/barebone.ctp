<?= $this->Html->docType('html5'); ?>
<html>
<head>
	<title><?= $title_for_layout ?></title>
	<?php
	echo $this->fetch('css');
	echo $this->Html->charset();
	echo $this->Layout->jQueryTag();
	?>
</head>
<body>
<div style="min-height: 100%; position: relative;">
	<?= $this->fetch('content'); ?>
</div>
</body>
</html>
