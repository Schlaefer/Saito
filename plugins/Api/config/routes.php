<?php

use Cake\Routing\Router;

// threads collection
// -------------------------------------

Router::scope(
    '/api/v1/',
    ['plugin' => 'Api'],
    function ($routes) {

        // must be first in scope to apply to following routes
        $routes->setExtensions(['json']);

        // entries
        // -------------------------------------

        // Read
        $routes->connect(
            'threads',
            [
                'controller' => 'ApiEntries',
                'action' => 'threadsGet',
                '_method' => 'GET'
            ]
        );

        // Read entries of thread
        $routes->connect(
            'threads/*',
            [
                'controller' => 'ApiEntries',
                'action' => 'threadsItemGet',
                '_method' => 'GET'
            ]
        );

        // Create
        $routes->connect(
            'entries',
            [
                'controller' => 'ApiEntries',
                'action' => 'entriesItemPost',
                '_method' => 'POST'
            ]
        );

        // Update
        $routes->connect(
            'entries/*',
            [
                'controller' => 'ApiEntries',
                'action' => 'entriesItemPut',
                '_method' => 'PUT'
            ]
        );

        // User
        // -------------------------------------

        // Login
        $routes->connect(
            'login',
            [
                'controller' => 'ApiUsers',
                'action' => 'login',
                '_method' => 'POST'
            ]
        );

        // Logout
        $routes->connect(
            'logout',
            [
                'controller' => 'ApiUsers',
                'action' => 'logout',
                '_method' => 'POST'
            ]
        );

        // Misc
        // -------------------------------------

        // Bootstrap - Read
        $routes->connect(
            'bootstrap',
            [
                'controller' => 'ApiCore',
                'action' => 'bootstrap',
                '_method' => 'GET'
            ]
        );

        // Mark as Read - Update
        $routes->connect(
            'markasread',
            [
                'controller' => 'ApiUsers',
                'action' => 'markasread',
                '_method' => 'POST'
            ]
        );

        // catchall for unknown route
        $routes->connect(
            '*',
            ['controller' => 'ApiCore', 'action' => 'unknownRoute']
        );
    }
);
