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
            'ParserPlugin' => \Plugin\BbcodeParser\src\Lib\Markup::class,
            /**
             * Upload directory root with trailing slash
             */
            'uploadDirectory' => WWW_ROOT . 'useruploads' . DIRECTORY_SEPARATOR,
            /**
             * Category-select in posting-form is prepopulated with a category
             *
             * - true - The first available category is preselected as default.
             * - false - The User is forced to select a category.
             */
            'answeringAutoSelectCategory' => false,
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
             * Log emails in debug.log instead of sending them.
             */
            'email' => false,
            /**
             * Log additional non-error information in info.log
             */
            'logInfo' => false,
        ],
    ]
];

/**
 * Uploader Configuration
 */

use ImageUploader\Lib\UploaderConfig;

$config['Saito']['Settings']['uploader'] = (new UploaderConfig())
    /**
     * Max number of uploads per user
     */
    ->setMaxNumberOfUploadsPerUser(20)
    /**
     * Max file size
     */
    ->setDefaultMaxFileSize('8MB')
    /**
     * Allowed mime/types
     */
    ->addType('audio/mpeg')
    ->addType('audio/mp4')
    ->addType('audio/ogg')
    ->addType('audio/opus')
    ->addType('audio/webm')
    ->addType('image/jpeg', '19MB')
    ->addType('image/png', '19MB')
    ->addType('image/svg+xml')
    ->addType('text/plain')
    ->addType('video/mp4')
    ->addType('video/webm');

return $config;
