<!DOCTYPE html>
<html>
<head>
    <title>Jasmine Spec Runner</title>

    <!-- include libs -->
    <?php
    echo $this->Layout->jQueryTag();

    echo $this->Html->css(
        // jasmin core
        ['../dev/node_modules/jasmine-core/lib/jasmine-core/jasmine']
    );
    echo $this->Html->script(
        [
            // jasmin core
            '../dev/node_modules/jasmine-core/lib/jasmine-core/jasmine',
            '../dev/node_modules/jasmine-core/lib/jasmine-core/jasmine-html',
            'tests/boot.js',
            //
            'lib/jquery-ui/jquery-ui.custom.min.js',
            '../dev/node_modules/bootstrap/docs/assets/js/bootstrap.js'
        ]
    );
    ?>
    <script>
        window.webroot = "<?= $this->request->getAttribute('webroot'); ?>"
    </script>
    <!-- include require.js -->
    <?php
    echo $this->RequireJs->scriptTag('test');
    ?>
</head>
<body></body>
</html>
