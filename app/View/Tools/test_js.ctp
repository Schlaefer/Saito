<!DOCTYPE html>
<html>
	<head>
			<title>Jasmine Spec Runner</title>

			<script type="text/javascript">
					var SaitoApp = {
							app: {
									settings: {
											webroot: "<?php echo $this->request->webroot; ?>"
									},
									runJsTests: true
							},
							settings: {
									embedly_enabled: '1'
							},
							currentUser: {
									user_show_inline: '0'
							}
					};
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
			<script>
				var require = {urlArgs: Math.floor(Math.random() * 1000000)};
			</script>
			<?php
				echo $this->RequireJs->scriptTag('main');
			?>
	</head>
	<body/>
</html>
