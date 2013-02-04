<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Jasmine Spec Runner</title>

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

    <!-- include Jasmin -->
		<?php
			echo $this->fetch('JasmineJs');
		?>

    <!-- include libs -->
		<?php
			echo $this->Html->script(
				array(
					'lib/jquery/jquery-1.9.0.js',
					'lib/jquery-ui/jquery-ui-1.9.2.custom.min.js',
					'bootstrap/bootstrap.js',
					'classes/thread_line.class.js',
					'_app.js',
					'lib/jquery.scrollTo/jquery.scrollTo-min.js',
				)
			);
		?>

    <!-- include source -->
		<?php
			echo $this->Html->script(
				array(
					'_app.js',
				)
			);
		?>

    <!-- include specs -->
		<?php
			echo $this->Html->script(
				array(
					// 'tests/BookmarkSpec.js',
					'tests/MarkItUpSpec.js'
				)
			);
		?>

		<!-- include require.js -->
		<?php
			echo $this->RequireJs->scriptTag('tests');
		?>

    <script type="text/javascript">
				/*
        (function() {
            var jasmineEnv = jasmine.getEnv();
            jasmineEnv.updateInterval = 1000;

            var htmlReporter = new jasmine.HtmlReporter();

            jasmineEnv.addReporter(htmlReporter);

            jasmineEnv.specFilter = function(spec) {
                return htmlReporter.specFilter(spec);
            };

            var currentWindowOnload = window.onload;

            window.onload = function() {
                if (currentWindowOnload) {
                    currentWindowOnload();
                }
                execJasmine();
            };

            function execJasmine() {
                jasmineEnv.execute();
            }

        })();
        */
    </script>

</head>

<body>
</body>
</html>
