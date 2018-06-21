<?php

/**
 * Saito Enduser Configuration
 */
$config = [
    'Saito' => [
        /**
         * Is the forum installed? Runs installer if not. Default: run installer.
         */
        'installed' => filter_var(
            env('INSTALLED', !file_exists(CONFIG . '/installer')),
            FILTER_VALIDATE_BOOLEAN
        ),
        /**
         * Is the forum up-to-date? Run updater if not. Default: run updater.
         */
        'updated' => filter_var(env('UPDATED', false), FILTER_VALIDATE_BOOLEAN),
        /**
         * Setting default language (mandatory)
         *
         * Compatibel to PHP's Locale. Implemented localizations:
         *
         * - de German
         * - en English
         *
         * @see http://php.net/manual/en/intro.intl.php
         * @see https://r12a.github.io/app-subtags/
         */
        'language' => 'en',

        'Settings' => [
            /**
             * Sets the markup parser
             *
             * Parser hould be placed in app/Plugin/<name>Parser
             */
            'ParserPlugin' => 'Bbcode',
            /**
             * Upload directory root with trailing slash
             */
            'uploadDirectory' => WWW_ROOT . 'useruploads' . DIRECTORY_SEPARATOR
        ],

        /**
         * Themes are plugins located in the plugins/ folder
         *
         * @see http://book.cakephp.org/3.0/en/views/themes.html
         */
        'themes' => [
            /**
             * Sets the default theme
             */
            'default' => 'Bota',

            /**
             * Array with additional themes available for all users
             */
            //'available' => ['MyTheme'],

            /**
             * Sets additional themes available for specific users only
             *
             * [<user-ID> => ['<theme name>', …], …]
             */
            // 'users' => [1 => ['TestTheme']]
        ],

        /**
         * Sets the X-Frame-Options header send with each request
         */
        'X-Frame-Options' => 'SAMEORIGIN',

        'Globals' => [
            /**
             * Empiric number matching the average number of postings per thread
             */
            'postingsPerThread' => 10
        ],
        'debug' => [
            /**
             * logs emails in debug.log instead of sending them
             */
            'email' => false
        ]
    ]
];

return $config;
