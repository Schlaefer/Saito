<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<title><?php echo $title_for_layout; ?></title>
			<?php
			echo $this->Html->css(array(
					'/install/css/blueprint/screen',
					'/install/css/install',
			));
			echo $scripts_for_layout;
			?>
	</head>

	<body>

    <div id="wrapper" class="install">
			<div id="main">
				<div id="install">
					<?php
					echo $this->Session->flash();
					echo $content_for_layout;
					?>
				</div>
			</div>
    </div>

	</body>
</html>