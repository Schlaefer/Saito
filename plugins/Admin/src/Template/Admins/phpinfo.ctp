<style>
    /* prevent ultra-width tables beyond layout */
    table { table-layout: fixed; }
    td { overflow-y: scroll; }
</style>

<?php

$this->Breadcrumbs->add(__('admin.sysInfo.h'), '/admin');
$this->Breadcrumbs->add(__('PHP Info'), false);

echo $this->Html->tag('h1', __('PHP Info'));

ob_start();
phpinfo();
$info = ob_get_clean();
if (preg_match('/\<body\>(?P<body>.*)\<\/body\>/is', $info, $matches)) {
    $body = $matches['body'];
    $body = preg_replace('/\<table\s?/', '<table class="table" ', $body);
    echo $body;
}
