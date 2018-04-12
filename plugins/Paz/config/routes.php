<?php
use Cake\Routing\Router;

Router::plugin('Paz', function ($routes) {
    $routes->fallbacks();
});
