<?php

/**
 * Email Config
 *
 * For config options see CakePHP 3 email configuration.
 *
 * @see http://book.cakephp.org/3.0/en/core-libraries/email.html#configuring-transports
 */
$config = [
    'Email' => [
        'saito' => [
            // reference to saito transport defined below
            'transport' => 'saito'
        ]
    ],
    'EmailTransport' => [
        'saito' => [
            // default: local PHP mailer
            'className' => 'Mail'
        ]
    ]
];

return $config;
