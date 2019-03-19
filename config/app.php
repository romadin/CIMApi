<?php
return [
    'debug_blacklist' => [
        '_ENV' => [
            'APP_KEY',
            'DB_PASSWORD',
        ],

        '_SERVER' => [
            'APP_KEY',
            'DB_PASSWORD',
        ],

        '_POST' => [
            'password',
        ],

        '_MAIL' => [
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
        ]
    ],
];