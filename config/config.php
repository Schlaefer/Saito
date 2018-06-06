<?php

define('PRODUCTION', true);
if (!PRODUCTION) {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ERROR);
}

$config = array();
$config['debug'] = !PRODUCTION;
$config['pages_order'] = 'page.folder:desc meta.date:asc meta.title:desc';
$config['encryptionKey'] = 'IdUFTSOnWZMnH2kddF[1wzKbDL1V[{m6MjedfTu4BqNdwTwz!SHSN8SOCcA9FP9v';
$config['site_title'] = 'Saito - The Threaded Web Internet Forum for PHP';
$config['theme'] = 'saito';

$config['plugins'] = array(
    'phile\\errorHandler' => array(
        'active' => !PRODUCTION,
        'handler' => 'development'
    ),
    // the default template engine
  //
    // the default data storage engine
    'phile\\rssFeed' => array('active' => true),
    'siezi\\phileTotalCache' => array('active' => PRODUCTION),
    'siezi\\phileServeContentFiles' => ['active' => true]
);
if (PRODUCTION) {
    $config['plugins']['phile\\phpFastCache'] = ['active' => true, 'storage' => 'files'];
    $config['plugins']['phile\\errorHandler']['handler'] = \Phile\Plugin\Phile\ErrorHandler\Plugin::HANDLER_ERROR_LOG;
}

return $config;
