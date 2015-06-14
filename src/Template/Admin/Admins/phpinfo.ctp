<?php

$this->Html->addCrumb(__('admin.sysInfo.h'), '/admin');
$this->Html->addCrumb(__('PHP Info'), '/admin/admins/phpinfo');

echo $this->Html->tag('h1', __('PHP Info'));

ob_start();
phpinfo();
$info = ob_get_clean();
if (preg_match('/\<body\>(?P<body>.*)\<\/body\>/is', $info, $matches)) {
    $body = $matches['body'];
    $body = str_replace('<table ', '<table class="table table-striped table-bordered" ', $body);
    echo $body;
}
