<!DOCTYPE html>
<html>
	<head>
			<title>Jasmine Spec Runner</title>

			<!-- include libs -->
			<?php
				echo $this->Layout->jQueryTag();

				echo $this->Html->css(
				// jasmin core
					['../dev/bower_components/jasmine/lib/jasmine-core/jasmine']
				);
				echo $this->Html->script([
						// jasmin core
						'../dev/bower_components/jasmine/lib/jasmine-core/jasmine',
						'../dev/bower_components/jasmine/lib/jasmine-core/jasmine-html',
						'tests/boot.js',
						// jasmin extensions
						'../dev/bower_components/jasmine-jquery/jasmine-jquery.js',
						'../dev/vendors/sinon-1.9.1.js',
						/*
						'../dev/bower_components/sinonjs-built/lib/sinon.js',
						'../dev/bower_components/sinonjs-built/lib/sinon/util/fake_xml_http_request.js',
						'../dev/bower_components/sinonjs-built/lib/sinon/util/fake_server.js',
						*/

						//
						'lib/jquery-ui/jquery-ui.custom.min.js',
						'bootstrap/bootstrap.js'
					]
				);
			?>
		<script>
			window.webroot = "<?= $this->request->webroot; ?>"
		</script>
		<style>
			/* filter out first result bar triggered from jasmine/boot.js
				 our results will come from manual require.js triggered tests
			.alert > .exceptions:first-of-type + .bar, .alert > .exceptions:first-of-type {
				display: none;
			}
				 */
		</style>

			<!-- include require.js -->
			<?php
				echo $this->RequireJs->scriptTag('test');
			?>
	</head>
	<body/>
</html>
