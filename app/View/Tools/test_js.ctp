<!DOCTYPE html>
<html>
	<head>
			<title>Jasmine Spec Runner</title>

			<script>
				window.webroot = "<?= $this->request->webroot; ?>"
			</script>

			<!-- include Jasmin -->
			<?php
				echo $this->fetch('JasmineJs');
			?>

			<!-- include libs -->
			<?php
				echo $this->Layout->jQueryTag();
				echo $this->Html->script(
					array(
						'lib/jquery-ui/jquery-ui-1.9.2.custom.min.js',
						'bootstrap/bootstrap.js'
					)
				);
			?>

			<!-- include require.js -->
			<?php
				echo $this->RequireJs->scriptTag('test');
			?>
	</head>
	<body/>
</html>
