<?php

	/**
	 * Saito Enduser Configuration
	 */
	$config = [
		'Saito' => [
			/**
			 * Setting default language (mandatory)
			 *
			 * Compatibel to PHP's Locale: http://php.net/manual/en/intro.intl.php
			 * So e.g. german would be: de
			 */
			'language' => 'en',

			'Settings' => [
				/**
				 * Sets the markup parser
				 *
				 * Parser hould be placed in app/Plugin/<name>Parser
				 */
				'ParserPlugin' => 'Bbcode'
			],

			/**
			 * Sets the default theme
			 */
			'themes' => [
				'default' => 'Paz',
			],

			/**
			 * Sets additional themes available for all users
			 *
			 * `*` - all installed themes (in Themed folder)
			 * `['A', 'B']` - only themes 'A' and 'B' (Themed folder names)
			 */
			// 'Saito.themes.available.all' => '*',

			/**
			 * Sets additional themes available for specific users only
			 *
			 * [<user-id> => '<theme name>', …]
			 */
			// 'Saito.themes.available.users' => [1 => ['C']],

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

			/**
			 * Users to notify via email if a new users registers successfully
			 *
			 * Provide an array with user IDs. To notify the admin (usually user-id 1):
			 *
			 *     [1]
			 *
			 * To notify the admin with id 1 and the user with the id 5:
			 *
			 *     [1, 5]
			 */
//			'Notification.userActivatedAdminNoticeToUserWithID' => [1],

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
