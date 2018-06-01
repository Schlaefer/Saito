<?php

/**
 * Saito Enduser Configuration
 */
$config = [
    'Saito' => [
        /**
         * Is forum installed? Runs installer if not.
         */
        'installed' => filter_var(
            env('INSTALLED', !file_exists(CONFIG . '/installer')),
            FILTER_VALIDATE_BOOLEAN
        ),
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
            'default' => 'Paz',

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

        /**
         * Add additional buttons to editor
         *
         * You can theme them with
         *
         * <code>
         *  .markItUp .markItUpButton<Id> a {
         *    …
         *  }
         * </code>
         *
         */
        /*
        'markItUp.additionalButtons' => [
            'Button1' => [
                // button-text
                'name' => 'Do Something',
                // hover title
                'title' => 'Button 1',
                // code inserted into text
                'code' => ':action:',

                // image in img/markitup/<icon-name>, replaces `name` (optional)
                'icon' => 'icon-name.png',
                // format replacement as image (optional)
                'type' => 'image',
                // replacement in output if type is image
                // image in img/markitup/<replacement>
                'replacement' => 'resultofbutton1.png'
            ],
            // …
        ],
        */

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
