<!DOCTYPE html>
<html>
<head>
	<title>QUnit Test Suite</title>
	<!-- Include QUnit files -->
	<link rel="stylesheet" href="http://code.jquery.com/qunit/qunit-git.css" type="text/css" media="screen">
	<script type="text/javascript" src="http://code.jquery.com/qunit/qunit-git.js"></script>
	<!-- Setup project environment -->
	<script type="text/javascript">
		var SaitoApp = {
			settings: {
				embedly_enabled: '1'
			},
			currentUser: {
				user_show_inline: '0'
			}
		};
	</script>
	<!-- Include project files -->
	<?php
	  echo $this->Html->script(
			array(
				'lib/jquery/jquery-1.9.0.js',
				'lib/jquery-ui/jquery-ui-1.9.2.custom.min.js',
				'bootstrap/bootstrap.js',
				'classes/thread_line.class.js',
				'_app.js',
				'lib/jquery.scrollTo/jquery.scrollTo-min.js',
				'tests/test.js' // Include test files
			)
		);
	?>

</head>
<body>
	<h1 id="qunit-header">QUnit Test Suite</h1>
	<h2 id="qunit-banner"></h2>
	<div id="qunit-testrunner-toolbar"></div>
	<h2 id="qunit-userAgent"></h2>
	<ol id="qunit-tests"></ol>
</body>
</html>
